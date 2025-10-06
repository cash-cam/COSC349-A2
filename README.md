# Student Grade Uploader and Checker
## Overview
This project deploys a student grade checker and administrator management web application entirely in the AWS cloud.
The system uses a three-tier architecture:
- UI EC2 instance — serves the front-end web interface (student & admin login).
- API EC2 instance — handles data operations and connects to the database.
- Amazon RDS (MySQL) — stores student, administrator, and grade data.
- Amazon S3 Bucket — holds RDS logs and database backups.

# Architecure 
```mermaid
flowchart LR
  Internet((Internet)) --> UI[UI EC2<br/>Apache + PHP]
  UI -- HTTP :80 --> API[API EC2<br/>Apache + PHP (Public/)]
  API -- MySQL :3306 --> RDS[(Amazon RDS MySQL<br/>studentdata)]

  RDS -. log exports .-> CW[CloudWatch Logs]
  CW -. export to S3 .-> S3[(Amazon S3<br/>Logs & Backups)]

  subgraph VPC [VPC 10.0.0.0/16]
    direction LR
    subgraph Public [Public Subnet]
      UI
    end
    subgraph Private [Private Subnet]
      API
      RDS
    end
  end
```

## Deployment Order 
*Required:*
- AWS Account (This was created in the learner lab enviroment)
- Key Pair saved (will need to create your own and save it somewhere safe)
- Access to repo

1. Create RDS Instance 
	- RDS Instance MySQL 8.0 (note the name you use for the instance i used 'studentdata')
2. Launch API EC2
	- Type t3.micro (ubuntu)
	- Subnet: Private
	- Attach API security group
	- 





