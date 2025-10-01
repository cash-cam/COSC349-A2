# Grade Tracker App for Studentes
- Distributed solution for students to be able to see their grades.
## structure
- VM1: **Reverse Proxy** Students can view their grades in the course, assignment/exam results, Average student grade of the course 
- VM2: **API** API service to connect the frontend to the database. Reads the endpoints
- VM3: **Storage** Database that persists students, courses and grades. 

## Technical
Vagrant Base: **bento/ubuntu 22.04**
I suggest at least 5GB of storage for cloning this project locally.
Open-source software used:
PHP, Apache, MySQL, Vagrant, VirtualBox, Bento Ubuntu

## How to run 
Firstly clone or fork the repository.
To run it is required that you have Vagrant [download here](https://developer.hashicorp.com/vagrant/install).
A Type 2 Hypervisor is also required such as [VirtualBox](https://www.virtualbox.org/wiki/Downloads) or [VMWare](https://www.vmware.com/).
Once the above downloads are complete use the below command within the repository.
```bash
vagrant up
``` 
To check that the prior worked
```bash
vagrant status
``` 
To access the VMs once running
```bash
vagrant ssh <vm-name>
```

Ideally you should be able to visit [this page](http://127.0.0.1:8080/index.php), and be displayed the application.
Test data is located in the schema.sql.

