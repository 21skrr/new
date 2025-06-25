-- Create the database and tables for the onboarding survey system
CREATE DATABASE IF NOT EXISTS onboarding_surveys;
USE onboarding_surveys;

-- Users table with role-based access
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('EMPLOYEE', 'SUPERVISOR', 'HR') NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Survey templates created by HR
CREATE TABLE survey_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  description TEXT,
  created_by INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Questions within survey templates
CREATE TABLE survey_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_id INT NOT NULL,
  question_text TEXT NOT NULL,
  question_type ENUM('multiple_choice', 'open_ended') NOT NULL,
  options TEXT, -- JSON format for multiple choice options
  question_order INT DEFAULT 1,
  FOREIGN KEY (template_id) REFERENCES survey_templates(id) ON DELETE CASCADE
);

-- Surveys assigned to employees by supervisors
CREATE TABLE assigned_surveys (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_id INT NOT NULL,
  employee_id INT NOT NULL,
  supervisor_id INT NOT NULL,
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  due_date DATE,
  status ENUM('pending', 'completed') DEFAULT 'pending',
  completed_at DATETIME NULL,
  FOREIGN KEY (template_id) REFERENCES survey_templates(id) ON DELETE CASCADE,
  FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employee responses to survey questions
CREATE TABLE survey_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  assigned_survey_id INT NOT NULL,
  question_id INT NOT NULL,
  answer TEXT NOT NULL,
  responded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (assigned_survey_id) REFERENCES assigned_surveys(id) ON DELETE CASCADE,
  FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
);

-- Insert sample users for testing
INSERT INTO users (full_name, email, password, role) VALUES
('John Employee', 'employee@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'EMPLOYEE'),
('Jane Supervisor', 'supervisor@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERVISOR'),
('Bob HR Manager', 'hr@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR');

-- Note: All sample passwords are 'password' (hashed)
