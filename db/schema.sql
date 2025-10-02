-- schema and basic insert values
CREATE TABLE students (
  student_id VARCHAR(8) PRIMARY KEY,
  name       TEXT NOT NULL,
  email      VARCHAR(255) UNIQUE
);

CREATE TABLE administrators (
  admin_id VARCHAR(8) PRIMARY KEY,
  name       TEXT NOT NULL,
  email      VARCHAR(255) UNIQUE
);

CREATE TABLE papers (
  code VARCHAR(7) PRIMARY KEY,
  name TEXT NOT NULL
);

CREATE TABLE enrolments (
  student_id VARCHAR(8) REFERENCES students(student_id) ON DELETE CASCADE,
  paper_code VARCHAR(7) REFERENCES papers(code)         ON DELETE CASCADE,
  PRIMARY KEY (student_id, paper_code)
);

CREATE TABLE assessments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  paper_code VARCHAR(7) NOT NULL REFERENCES papers(code) ON DELETE CASCADE,
  name  VARCHAR(80) NOT NULL, 
  type  TEXT NOT NULL CHECK (type IN ('lab','assignment','test','exam')),
  weight NUMERIC(4,3) NOT NULL CHECK (weight >= 0 AND weight <= 1),
  max_points NUMERIC(6,2) NOT NULL CHECK (max_points > 0),
  due_date DATE,
  UNIQUE (paper_code, name)
);




CREATE TABLE grades (
  student_id VARCHAR(8) NOT NULL REFERENCES students(student_id)   ON DELETE CASCADE,
  assessment_id INT NOT NULL REFERENCES assessments(id)        ON DELETE CASCADE,
  points NUMERIC(6,2) NOT NULL CHECK (points >= 0),
  PRIMARY KEY (student_id, assessment_id)
);

-- The majority of the below INSERT statements are AI Generated

INSERT INTO students (student_id, name, email) VALUES
('S0000001', 'Alice Smith', 'alice@example.edu'),
('s1', 'Alice Smith', 'cam@edu'),
('S0000002', 'Bob Jones',   'bob@example.edu'),
('S0000003','Alex Li','alex.li@example.com'),
('S0000004','Priya Patel','priya.patel@example.com'),
('S0000005','James Smith','james.smith@example.com'),
('S0000006','Sophie Brown','sophie.brown@example.com'),
('S0000007','Oliver Jones','oliver.jones@example.com'),
('S0000008','Mia Thompson','mia.thompson@example.com');


INSERT INTO papers (code, name) VALUES
('COSC349', 'Cloud Systems'),
('COSC326', 'Computational Problem Solving'),
('COSC343','Artificial Intelligence'),
('COSC345','Human–Computer Interaction'),
('INFO202','Business Information Systems');


INSERT INTO enrolments (student_id, paper_code) VALUES
('S0000001', 'COSC349'),
('S0000002', 'COSC349'),
('S0000001','COSC343'),
('S0000002','COSC343'),
('S0000003','COSC349'),
('S0000003','COSC343'),
('S0000003','INFO202'),
('S0000004','COSC349'),
('S0000004','COSC345'),
('S0000005','COSC345'),
('S0000005','COSC343'),
('S0000006','COSC349'),
('S0000006','INFO202'),
('S0000007','INFO202'),
('S0000007','COSC343'),
('S0000008','COSC345');

INSERT INTO assessments (id, paper_code, name, type, weight, max_points, due_date) VALUES
(1, 'COSC349', 'Assignment 1', 'assignment', 0.40, 100, '2025-08-30'),
(2, 'COSC349', 'Final Exam',   'exam',       0.60, 200, '2025-10-30'),
(3, 'COSC343', 'Assignment 1', 'assignment', 0.30, 100, '2025-08-25'),
(4, 'COSC343', 'Project', 'assignment', 0.30, 100, '2025-09-20'),
(5, 'COSC343', 'Final Exam',   'exam',       0.40, 200, '2025-10-28'),


(6, 'COSC345', 'Assignment 1', 'assignment', 0.20, 100, '2025-08-22'),
(7, 'COSC345', 'Assignment 2', 'assignment', 0.30, 100, '2025-09-15'),
(8, 'COSC345', 'Final Exam',   'exam',       0.50, 200, '2025-10-30'),


(9,  'INFO202', 'Case Study',  'assignment', 0.40, 100, '2025-09-05'),
(10, 'INFO202', 'Final Exam',  'exam',       0.60, 150, '2025-10-29');


-- COSC349
INSERT INTO grades (student_id, assessment_id, points) VALUES
('S0000003', 1,  76),
('S0000003', 2, 165),
('S0000004', 1,  92),
('S0000004', 2, 180),
('S0000006', 1,  68),
('S0000006', 2, 140);

INSERT INTO grades (student_id, assessment_id, points) VALUES
('S0000001', 3,  85),
('S0000001', 4,  78),
('S0000001', 5, 150),

('S0000002', 3,  74),
('S0000002', 4,  81),
('S0000002', 5, 142),

('S0000003', 3,  88),
('S0000003', 4,  90),
('S0000003', 5, 168),

('S0000005', 3,  79),
('S0000005', 4,  82),
('S0000005', 5, 160),

('S0000007', 3,  70),
('S0000007', 4,  75),
('S0000007', 5, 130);

-- COSC345
INSERT INTO grades (student_id, assessment_id, points) VALUES
('S0000004', 6,  78),
('S0000004', 7,  83),
('S0000004', 8, 155),

('S0000005', 6,  90),
('S0000005', 7,  88),
('S0000005', 8, 170),

('S0000008', 6,  84),
('S0000008', 7,  79),
('S0000008', 8, 149);

-- INFO202
INSERT INTO grades (student_id, assessment_id, points) VALUES
('S0000003',  9, 72),
('S0000003', 10, 110),

('S0000006',  9, 66),
('S0000006', 10, 105),

('S0000007',  9, 77),
('S0000007', 10, 120);

