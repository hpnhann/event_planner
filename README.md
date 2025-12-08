Volunteer Management System (VMS)
Centralized Platform for University Volunteer Activity Management
🌟 Introduction
The Volunteer Management System (VMS) is a web application designed to replace the scattered, general-purpose tools (such as Google Sheets and chat groups) currently used to manage volunteer activities within university faculties.
Our goal is to centralize the entire volunteer lifecycle—from event creation to performance tracking—to minimize manual administrative work, improve transparency in reporting
✨ Key Features
The system supports three main user roles: Admin/Coordinator, Organizer, and Volunteer.
1. System Management & Reporting (Admin)
Comprehensive Member/Activity Control: Create, Edit, and Delete members and activities.
Full Data Oversight: Access and Export Reports (CSV/Excel) and view the master registration list for all activities.
2. Event Coordination & Execution (Organizer)
Attendance Automation: View real-time registration lists and perform secure Check Attendance (Check-in/Check-out).
Task Management: Manage task boards, create/edit tasks, assign tasks to team members, and update task progress.
Role-Specific Access: Access registration details only for assigned activities.
3. Volunteer Participation & Tracking (Volunteer)
Seamless Registration: View event details, Register for Activity, and manage personal registration history.
Personal Tracking: Maintain and update personal profile information.
4. General Access (Guest)
View public Activity List and details.
Securely Authenticate (Log in) to the system.

⚙️To run this project locally, please follow these steps:
Clone the Repository:
git clone [https://github.com/hpnhann/event_planner.git]
cd volunteer-management-system
Install Dependencies:
# For the Backend (e.g., Node.js)
npm install
# For the Frontend (e.g., React)
# cd client && npm install
Environment Configuration:
Create a .env file in the root directory.
Add the required environment variables (e.g., database connection string, security key):
DATABASE_URL=...
SECRET_KEY=...
# Add other variables...
Start the Application:
# Start the Backend Server
npm start
# Start the Frontend (if separate)
# cd client && npm start
The application should be available at http://localhost:[YOUR_PORT].