# Smart Residence ERP
**Digital Ecosystem for Modern Residential Management**

## Project Overview
Smart Residence ERP is a web-based management system designed to streamline the daily operations of a residential society. It bridges the gap between residents and the administration by providing a digital platform for service bookings, complaint redressal, document management, and community notices.

The system features a dual-interface architecture:
1.  **User Portal**: For residents to avail services and manage their profile.
2.  **Admin Portal**: For administrators to control operations, verify documents, and manage the ecosystem.

## Technology Stack
-   **Frontend**: HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5.3
-   **Backend**: PHP 8.x (Native/Vanilla)
-   **Database**: MySQL
-   **Icons**: FontAwesome 6.4

## Key Features

### 🔐 Authentication & Security
-   Secure Login & Registration System.
-   Role-Based Authorization (Admin vs User).
-   Password Hashing (Bcrypt).
-   Session Management with Timeout Protection.

### 👤 User Module (Resident)
-   **Interactive Dashboard**: Real-time overview of active requests, pending payments, and notices.
-   **Service Booking**: Browse and book services like AC Repair, Plumbing, Cleaning, etc.
-   **Document Vault**: Securely upload and store residence documents (Lease, ID, etc.) for admin verification.
-   **Complaint Box**: File complaints directly to the administration and track status.
-   **Profile Management**: Update personal details and manage app themes (Light/Dark mode).
-   **My Requests**: History and status tracking of all booked services.

### 🛡️ Admin Module (Super Admin)
-   **Command Center Dashboard**: High-level stats, recent activity, and system health monitoring.
-   **Service Management**: CRUD operations for the Service Catalog (Add/Edit/Remove services).
-   **Request Handling**: Full workflow control (Pending -> Approved -> Completed/Rejected).
-   **User Directory**: View and manage registered residents (Delete access).
-   **Document Verification**: Review uploaded documents and Approve/Reject them.
-   **Notice Board**: Broadcast digital notices to all residents.
-   **Complaints**: Review and resolve resident grievances.

## Recent Enhancements
-   **Simplified Architecture**: Streamlined to a single-tier Super Admin structure for clear control.
-   **Theming Engine**: Robust Light/Dark mode with user preference persistence across sessions.
-   **Responsive Design**: Mobile-friendly sidebar and layout adaptations.
-   **UI Polish**: Glassmorphism effects, animated interactive elements, and premium typography.
-   **Stability**: production-grade error handling (silent logging) and database optimization.

## Setup Instructions

1.  **Database**:
    -   Create a database named `smart_residence_erp`.
    -   Import `database.sql` to set up tables.
2.  **Configuration**:
    -   Verify credentials in `config/db.php`.
3.  **Run**:
    -   Host the folder on a PHP server (XAMPP/Apache).
    -   Access via `http://localhost/smart-erp`.
