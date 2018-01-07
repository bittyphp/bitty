<?php

namespace Bizurkur\Bitty\Tests\Http;

use Bizurkur\Bitty\CollectionInterface;
use Bizurkur\Bitty\Http\UploadedFile;
use Bizurkur\Bitty\Http\UploadedFileCollection;
use Bizurkur\Bitty\Tests\TestCase;

class UploadedFileCollectionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new UploadedFileCollection([]);

        $this->assertInstanceOf(CollectionInterface::class, $fixture);
    }

    /**
     * @dataProvider sampleFileData
     */
    public function testLoadingFiles($files, $expectedData)
    {
        $expected = $this->getExpectedData($expectedData);

        $fixture = new UploadedFileCollection($files);
        $actual  = $fixture->all();

        $this->assertEquals($expected, $actual);
    }

    public function sampleFileData()
    {
        $fieldA = uniqid('field');
        $fieldB = uniqid('field');
        $fieldC = uniqid('field');
        $pathA  = uniqid('path');
        $pathB  = uniqid('path');
        $pathC  = uniqid('path');
        $nameA  = uniqid('name');
        $nameB  = uniqid('name');
        $nameC  = uniqid('name');
        $typeA  = uniqid('type');
        $typeB  = uniqid('type');
        $typeC  = uniqid('type');
        $sizeA  = rand();
        $sizeB  = rand();
        $sizeC  = rand();
        $errorA = rand();
        $errorB = rand();
        $errorC = rand();

        return [
            'single file, one field' => [
                'files' => [
                    $fieldA => [
                        'tmp_name' => $pathA,
                        'name' => $nameA,
                        'type' => $typeA,
                        'size' => $sizeA,
                        'error' => $errorA,
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        'tmp_name' => $pathA,
                        'name' => $nameA,
                        'type' => $typeA,
                        'size' => $sizeA,
                        'error' => $errorA,
                    ],
                ],
            ],
            'multiple files, one field' => [
                'files' => [
                    $fieldA => [
                        'tmp_name' => [$pathA, $pathB],
                        'name' => [$nameA, $nameB],
                        'type' => [$typeA, $typeB],
                        'size' => [$sizeA, $sizeB],
                        'error' => [$errorA, $errorB],
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        [
                            'tmp_name' => $pathA,
                            'name' => $nameA,
                            'type' => $typeA,
                            'size' => $sizeA,
                            'error' => $errorA,
                        ],
                        [
                            'tmp_name' => $pathB,
                            'name' => $nameB,
                            'type' => $typeB,
                            'size' => $sizeB,
                            'error' => $errorB,
                        ],
                    ],
                ],
            ],
            'single file, multiple fields' => [
                'files' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                'tmp_name' => $pathA,
                                'name' => $nameA,
                                'type' => $typeA,
                                'size' => $sizeA,
                                'error' => $errorA,
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                'tmp_name' => $pathA,
                                'name' => $nameA,
                                'type' => $typeA,
                                'size' => $sizeA,
                                'error' => $errorA,
                            ],
                        ],
                    ],
                ],
            ],
            'multiple files, multiple fields' => [
                'files' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                'tmp_name' => [$pathA, $pathB],
                                'name' => [$nameA, $nameB],
                                'type' => [$typeA, $typeB],
                                'size' => [$sizeA, $sizeB],
                                'error' => [$errorA, $errorB],
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        $fieldB => [
                            $fieldC => [
                                [
                                    'tmp_name' => $pathA,
                                    'name' => $nameA,
                                    'type' => $typeA,
                                    'size' => $sizeA,
                                    'error' => $errorA,
                                ],
                                [
                                    'tmp_name' => $pathB,
                                    'name' => $nameB,
                                    'type' => $typeB,
                                    'size' => $sizeB,
                                    'error' => $errorB,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'mixed' => [
                'files' => [
                    $fieldA => [
                        $fieldB => [
                            'tmp_name' => [$pathA, $pathB],
                            'name' => [$nameA, $nameB],
                            'type' => [$typeA, $typeB],
                            'size' => [$sizeA, $sizeB],
                            'error' => [$errorA, $errorB],
                        ],
                    ],
                    $fieldC => [
                        'tmp_name' => $pathC,
                        'name' => $nameC,
                        'type' => $typeC,
                        'size' => $sizeC,
                        'error' => $errorC,
                    ],
                ],
                'expected' => [
                    $fieldA => [
                        $fieldB => [
                            [
                                'tmp_name' => $pathA,
                                'name' => $nameA,
                                'type' => $typeA,
                                'size' => $sizeA,
                                'error' => $errorA,
                            ],
                            [
                                'tmp_name' => $pathB,
                                'name' => $nameB,
                                'type' => $typeB,
                                'size' => $sizeB,
                                'error' => $errorB,
                            ],
                        ],
                    ],
                    $fieldC => [
                        'tmp_name' => $pathC,
                        'name' => $nameC,
                        'type' => $typeC,
                        'size' => $sizeC,
                        'error' => $errorC,
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds a similar array structure as to what is expected.
     *
     * @param mixed[] $files
     *
     * @return UploadedFile[]
     */
    protected function getExpectedData(array $files)
    {
        $expected = [];

        foreach ($files as $field => $file) {
            if (!array_key_exists('error', $file)) {
                $expected[$field] = $this->getExpectedData($file);
            } else {
                $expected[$field] = new UploadedFile(
                    $file['tmp_name'],
                    $file['name'],
                    $file['type'],
                    $file['size'],
                    $file['error'],
                    true
                );
            }
        }

        return $expected;
    }
}
