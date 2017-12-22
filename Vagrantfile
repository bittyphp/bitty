# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.box = "ubuntu/trusty32"
    config.vm.box_check_update = false

    config.vm.network "forwarded_port", guest: 80, host: 8080, auto_correct: true
    config.vm.network "private_network", ip: "10.10.10.10"
    config.vm.synced_folder "./", "/var/www"
    # config.vm.synced_folder "./", "/var/www", type: "rsync",
    #     rsync__exclude: ".git/",
    #     rsync__args: ["--verbose", "--rsync-path='sudo rsync'", "--archive", "--delete", "-z"]
    config.vm.hostname = "bitty"
    config.vm.provision :shell, path: "data/provision.sh"
end
