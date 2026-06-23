# 🏥 Ivor Paine Memorial Hospital — Management System

A full-stack hospital management system built with **PHP 8.1**, **SQL Server 2022**, and **Docker**.  
Includes a web dashboard for managing wards, beds, doctors, nurses, and patients, plus 12 analytical SQL reports.

## Tech Stack
- **Backend:** PHP 8.1 + sqlsrv extension
- **Database:** Microsoft SQL Server 2022 (T-SQL)
- **Frontend:** PHP/HTML/CSS/JavaScript
- **Infrastructure:** Docker + docker-compose

## Features
- Ward, bed, and care unit management
- Doctor and nurse records with role-based positions
- Patient admission and medical history tracking
- Treatment and complaint logging
- **12 analytical reports** including:
  - Consultant team listings
  - Doctor performance grades
  - Patient treatment history
  - Staff position counts
  - Ward staff breakdowns

## Database Schema
Tables: `Ward`, `Bed`, `CareUnit`, `Doctor`, `Consultant`, `Nurse`, `Patient`, `Complaint`, `Treatment`, `MedicalHistory`, `PrevExperience`, `PerfGrade`

## Run Locally

**Requirements:** Docker Desktop installed and running.

```bash
git clone https://github.com/AliAmmar512/ivor-hospital
cd ivor-hospital
cp .env.example .env
# Open .env and set your SA_PASSWORD
docker-compose up --build
```

Then open: **http://localhost:8080/ivor_hospital**

## Docker Hub
Pull the pre-built image directly:

```bash
docker pull aliammar572/ivor-hospital-web:latest


```
## Run Without Cloning

If you just want to run the app without the full source code:

**1. Download these 3 files from this repo:**
- `docker-compose.hub.yml`
- `init-db.sh`
- `ivor_hospital_ddl.sql`

**2. Create a `.env` file:**
```bash
SA_PASSWORD=Admin123!
DB_USER=sa
DB_NAME=IvorHospital
```

**3. Run:**
```bash
docker-compose -f docker-compose.hub.yml up
```

Then open: **http://localhost:8080/ivor_hospital**