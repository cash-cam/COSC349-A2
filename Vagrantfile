# -*- mode: ruby -*-
# vi: set ft=ruby :


Vagrant.configure("2") do |config|
  # Base box for the VM's
  config.vm.box = "bento/ubuntu-22.04"
  
  # Use standard Vagrant Key
  config.ssh.insert_key = false
  # config.ssh.forward_agent = false
  # config.vm.usable_port_range = (2200..2299)

  # Boot Timeout
  config.vm.boot_timeout = 120
  # Provider speciication, alternative "vmware_desktop"
  config.vm.provider "virtualbox" do |vb|
  # vb.gui = true #chatg (If want to see vm console windows then uncomment)
  end

  # VM1: Frontend (Apache proxy)
  # Exposes :80 to host (8080) and proxies to the API VM

  config.vm.define "frontend" do |fe|
    fe.vm.hostname = "frontend"
    fe.vm.network "private_network", ip: "192.168.56.11"
    fe.vm.network "forwarded_port", guest: 80, host: 8080, host_ip: "127.0.0.1"
    fe.vm.network "forwarded_port", id: "ssh", guest: 22, host: 2223, auto_correct: true
    
    fe.vm.synced_folder ".", "/vagrant", owner: "vagrant", group: "vagrant", mount_options: ["dmode=775,fmode=777"]
    fe.vm.provision "shell", path: "build-frontend.sh"
  end 



  # VM2: API (Apache + PHP)
  # Runs PHP application that talks to MySQL on VM3
  config.vm.define "api" do |api|
    api.vm.hostname = "api"
    api.vm.network "private_network", ip: "192.168.56.12"    
    api.vm.network "forwarded_port", id: "ssh", guest: 22, host: 2224, auto_correct: true
    api.vm.synced_folder ".", "/vagrant", owner: "vagrant", group: "vagrant", mount_options: ["dmode=775,fmode=777"]

    api.vm.provision "shell", path: "build-api.sh"
  end 



  # VM3: Database (MySQL)
  # Schema + seed data. Grants access to API BM by its private IP
  config.vm.define "database" do |db|
      db.vm.hostname = "database"
      db.vm.network "private_network", ip: "192.168.56.13"
      db.vm.network "forwarded_port", id: "ssh", guest: 22, host: 2225, auto_correct: true

    
      db.vm.provision "shell", path: "build-database.sh"
  end 
    config.vm.provision "shell", inline: <<-'SHELL'
      echo "The 3 VM's are working! You can access the web interface at 127.0.0.1:8080/index.php"
    SHELL
end    


  