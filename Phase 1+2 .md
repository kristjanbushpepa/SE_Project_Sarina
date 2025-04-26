## Team Information

### Team Name: Sarina Highschool Interactive System

### Team Leader:
- **Name:** Kristjan Bushpepa
- **GitHub Username:** kristjanbushpepa
- **Email:** kbushpepa22@epoka.edu.al

### Team Members:
1. Ergita Hoxha - GitHub: ergitahoxha -  Email: erhoxha22@epoka.edu.al
2. Armand Cera - GitHub: armandcera - Email: acera22@epoka.edu.al
3. Helga Mali - GitHub: helgamali - Email: hmali22@epoka.edu.al


---

## Project Details

### Project Title: Sarina Highschool Interactive System

### Problem Statement:
Sarina Highschool in Tirana, Albania, faces challenges in communication between parents, students, and school staff. Parents struggle to monitor their children's academic performance, students lack a structured way to access schedules and assignments, and teachers require a more efficient method for organizing parent meetings and school announcements. The absence of a centralized, digital platform leads to miscommunication, missed deadlines, and inefficiencies in school management.

### Solution Proposed:
Sarina Highschool Interactive System will be a **comprehensive school management web platform** that enables **real-time communication and data sharing** between students, parents, and school staff. The system will include:
- **Real-time grade tracking:** Parents and students can monitor academic progress with progress graphs and breakdowns.
- **Timetables and class schedules:** Students can view class schedules, exams, and school activities.
- **School announcements and event notifications:** Ensuring that parents and students stay informed about school events, holidays, and policy changes.
- **Direct communication system:** A secure messaging feature for students, teachers, and parents to interact efficiently.
- **Parent-teacher meeting scheduling:** Allowing parents to book meetings with teachers through the system.
- **Homework and assignments tracking:** Students can access, submit assignments digitally, and receive feedback from teachers.
- **Extracurricular activity management:** Students and parents can enroll in school clubs and activities.
- **Personalized student dashboard:** Displays grades, upcoming assignments, and important notifications.
- **Attendance tracking:** Students and parents can monitor attendance records and request makeup classes.
- **Discussion forum:** A space where students can interact with teachers and peers for academic collaboration.
- **Teacher feedback and class resources:** Teachers can upload lesson plans, syllabi, and study materials for students to access.

### Project Scope:
- **Aim:** To develop a **user-friendly, web-based** school management system for Sarina Highschool, improving academic transparency, communication, and organization.
- **Main Objectives:**
  1. Implement a secure **login system** for students, parents, and teachers.
  2. Develop a **dashboard** for students and parents to track grades, attendance, and assignments.
  3. Enable a **messaging system** for parents and teachers.
  4. Create an **event and school calendar** to manage schedules.
  5. Provide a **secure, compliant system** to protect student data and maintain privacy standards.
  6. Integrate a **forum for academic discussions** between students and teachers.
  7. Implement **attendance tracking features** for students and staff.
  8. Develop a **resource-sharing system** for teachers to upload class materials.

### Application Description:
The Sarina Highschool Interactive System will serve as a **centralized online platform** accessible via web browsers and mobile devices. It will integrate **modern web technologies** to ensure scalability, security, and usability. The system will follow **Albanian education policies and privacy standards** to protect sensitive student data.

---

## Technology Stack
The system will be developed using the following technologies:
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Security Measures:** Data encryption, secure authentication, access control

---

## Roles and Tasks Distribution

### Team Leader:
- **Kristjan Bushpepa:** Project coordination, Full Stack Development (Frontend & Backend), ensuring a user-friendly experience and robust backend functionality, repository management, team communication, and frontend support.

### Main Roles and Tasks:
1. **Ergita Hoxha** - Full Stack Development (Frontend & Backend),UI/UX design
2. **Armand Cera** - Backend Development, Database Management.
3. **Helga Mali** - Full Stack Development (Frontend & Backend),, Database Management, Testing, Documentation, and System Security, ensuring compliance with **education and data privacy standards**.

## Additional Notes
- The system will follow **agile development methodologies** for iterative improvements.
- **Security measures** such as **data encryption and access control** will be implemented to ensure student privacy.

# Deadline
Submission Deadline: 15.03.2025, 23:59 hours.

## GitHub Repository
https://github.com/kristjanbushpepa/SE_Project_Sarina

---
# Phase II: User Requirements and Application Specifications

**Submission Deadline:** 26.03.2025, 23:59

## 1. Chosen Development Model:
### Agile
Agile was selected due to its flexibility and iterative development approach. Given that user needs may evolve throughout the development cycle, Agile allows for continuous feedback, frequent testing, and easy adaptation. This is ideal for a school-based system where different stakeholders (students, parents, staff) may suggest improvements mid-project.

## 2. User Requirements:
### a. Stakeholders:
- **Students** - Use the platform to check schedules, assignments, and grades.
- **Parents** - Monitor academic progress and communicate with teachers.
- **Teachers** - Upload grades, manage resources, and communicate with students/parents.
- **School Administrators** - Oversee system usage, manage content, and configure policies.
- **Developers** - Build and maintain the system.

### b. User Stories:
- **Student**:  
  *“As a student, I want to view my grades in a dashboard so that I can track my academic performance.”*

- **Parent**:  
  *“As a parent, I want to schedule meetings with teachers through the platform so that I can stay informed about my child’s progress.”*

- **Teacher**:  
  *“As a teacher, I want to post assignments and study resources so that students can access them easily.”*

- **Admin**:  
  *“As an admin, I want to manage all user roles and access so that the platform remains secure and functional.”*

## 3. Functional Requirements:
### a. Brief Description:
The system should allow users to log in securely. Students and parents can view academic data (grades, attendance, assignments). Teachers can post assignments and manage grades. The system should support event notifications and school calendar integration. A messaging system for secure communication. Admins can manage users and permissions.

### b. Acceptance Criteria:
- **Login System**
  - Users can log in with valid credentials.
  - Incorrect credentials are rejected.
  - Session expires after inactivity.
  
- **Grade Dashboard**
  - Students can see subjects and grades.
  - Graph displays grade progress over time.
  - Parents have view-only access.

- **Messaging System**
  - Users can send/receive messages.
  - Notification is sent on new messages.
  - Messages are stored securely.

## 4. Non-Functional Requirements:
### a. Brief Description:
- **Performance:** The system should load within 2 seconds on average.
- **Usability:** Interfaces should be easy to navigate for non-tech-savvy users.
- **Scalability:** Should handle increasing number of users without performance drop.
- **Security:** All data must be encrypted and follow data privacy laws.

### b. Acceptance Criteria:
- **Speed:** Pages load in under 3 seconds 95% of the time.
- **Ease of Use:** At least 70% of test users can complete key tasks without help.
- **Security:** All passwords hashed, sensitive data encrypted; access control in place.
- **Uptime:** System available 99% of the time during school hours.

## 5. Application Specifications:
### a. Architecture:
Provide an overview of the chosen architecture:
- Include high-level diagrams or descriptions of system components and their interactions.

### b. Database Model:
- **Tables:**
  - **Users:** id, name, role, email, password
  - **Admin** id, name, role, email, password
  - **Grades:** id, student_id, subject, score
  - **Assignments:** id, title, description, deadline, student_id
  - **Messages:** sender_id, receiver_id, content, timestamp
  - **Attendance:** student_id, date, status
  - **Events:** title, description, date

- **Relationships:**
  - One-to-many between Users and Grades, Assignments.
  - Many-to-many between Users (via Messages).

### c. Technologies Used:
- **Frontend:** HTML, CSS, JavaScript – For responsive and accessible interfaces.
- **Backend:** PHP – Well-supported and integrates with MySQL.
- **Database:** MySQL – Reliable relational database suited for structured school data.
- **Security:** HTTPS, hashed passwords, encrypted communications, role-based access control.

### d. User Interface Design:
- Login Page
- Student Dashboard
- Parent Dashboard
- Teacher Dashboard
- Messaging Interface
- Admin Panel

### e. Security Measures:
- **Authentication:** Role-based login system.
- **Encryption:** All sensitive data encrypted using standard algorithms.
- **Authorization:** Access control per user role.
- **HTTPS:** All data transmitted over secure channels.
- **Data Compliance:** Follows Albania’s educational data privacy regulations.



