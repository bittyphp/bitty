<?php

namespace Bitty\Http;

use Bitty\Collection;
use Bitty\Http\UploadedFile;

class UploadedFileCollection extends Collection
{
    /**
     * Normalizes an array of $_FILES into a usable collection of UploadedFiles.
     *
     * @param array $files
     */
    public function __construct(array $files)
    {
        $this->data = $this->collapseFileTree($files);
    }

    /**
     * Collapses a file tree into a usable structure.
     *
     * A single file entry might be one file, an array of files, or an array
     * of infinite arrays of files.
     *
     * That input here is expected to match that of $_FILES.
     *
     * @see http://www.php-fig.org/psr/psr-7/#16-uploaded-files
     *
     * @param array $files
     *
     * @return array
     */
    protected function collapseFileTree(array $files)
    {
        $tree = [];

        foreach ($files as $field => $file) {
            // array of array of files
            if (!isset($file['error'])) {
                if (is_array($file)) {
                    $tree[$field] = $this->collapseFileTree($file);
                }

                continue;
            }

            // single file
            if (!is_array($file['error'])) {
                $tree[$field] = new UploadedFile(
                    isset($file['tmp_name']) ? $file['tmp_name'] : null,
                    isset($file['name']) ? $file['name'] : null,
                    isset($file['type']) ? $file['type'] : null,
                    isset($file['size']) ? $file['size'] : null,
                    isset($file['error']) ? $file['error'] : null,
                    true
                );

                continue;
            }

            // array of files
            $list = [];
            foreach ($file['error'] as $index => $junk) {
                $list[] = new UploadedFile(
                    isset($file['tmp_name'][$index]) ? $file['tmp_name'][$index] : null,
                    isset($file['name'][$index]) ? $file['name'][$index] : null,
                    isset($file['type'][$index]) ? $file['type'][$index] : null,
                    isset($file['size'][$index]) ? $file['size'][$index] : null,
                    isset($file['error'][$index]) ? $file['error'][$index] : null,
                    true
                );
            }

            $tree[$field] = $list;
        }

        return $tree;
    }
}
