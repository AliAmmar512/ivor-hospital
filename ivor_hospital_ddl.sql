CREATE DATABASE IvorHospital;
GO

USE IvorHospital;
GO

CREATE TABLE Ward (
    WardID      INT           NOT NULL,
    WardName    VARCHAR(100)  NOT NULL,
    Specialty   VARCHAR(100)  NOT NULL,
    CONSTRAINT PK_Ward PRIMARY KEY (WardID),
    CONSTRAINT UQ_Ward_Name UNIQUE (WardName)
);

CREATE TABLE Bed (
    BedNo   INT  NOT NULL,
    WardID  INT  NOT NULL,
    CONSTRAINT PK_Bed PRIMARY KEY (BedNo),
    CONSTRAINT FK_Bed_Ward FOREIGN KEY (WardID) REFERENCES Ward(WardID)
);

CREATE TABLE CareUnit (
    CareUnitNo  INT  NOT NULL,
    WardID      INT  NOT NULL,
    CONSTRAINT PK_CareUnit PRIMARY KEY (CareUnitNo),
    CONSTRAINT FK_CareUnit_Ward FOREIGN KEY (WardID) REFERENCES Ward(WardID)
);

CREATE TABLE Doctor (
    DoctorID        INT           NOT NULL,
    Name            VARCHAR(150)  NOT NULL,
    Position        VARCHAR(50)   NOT NULL CHECK (Position IN ('s', 'jh', 'sh', 'ar', 'r')),
    DateJoinedTeam  DATE          NULL,
    ConsultantID    INT           NULL,
    CONSTRAINT PK_Doctor PRIMARY KEY (DoctorID)
);

CREATE TABLE Consultant (
    ConsultantID  INT           NOT NULL,
    Specialty     VARCHAR(100)  NOT NULL,
    CONSTRAINT PK_Consultant PRIMARY KEY (ConsultantID),
    CONSTRAINT FK_Consultant_Doctor FOREIGN KEY (ConsultantID) REFERENCES Doctor(DoctorID)
);

ALTER TABLE Doctor
    ADD CONSTRAINT FK_Doctor_Consultant FOREIGN KEY (ConsultantID) REFERENCES Consultant(ConsultantID);

CREATE TABLE Nurse (
    NurseID     INT           NOT NULL,
    Name        VARCHAR(150)  NOT NULL,
    Position    VARCHAR(50)   NOT NULL CHECK (Position IN ('day_sister', 'night_sister', 'staff_nurse', 'non_registered')),
    WardID      INT           NOT NULL,
    CareUnitNo  INT           NULL,
    CONSTRAINT PK_Nurse PRIMARY KEY (NurseID),
    CONSTRAINT FK_Nurse_Ward FOREIGN KEY (WardID) REFERENCES Ward(WardID),
    CONSTRAINT FK_Nurse_CareUnit FOREIGN KEY (CareUnitNo) REFERENCES CareUnit(CareUnitNo)
);

ALTER TABLE CareUnit
    ADD InChargeNurseID INT NULL;

ALTER TABLE CareUnit
    ADD CONSTRAINT FK_CareUnit_HeadNurse FOREIGN KEY (InChargeNurseID) REFERENCES Nurse(NurseID);

CREATE TABLE Patient (
    PatientNo    INT           NOT NULL,
    PatientName  VARCHAR(150)  NOT NULL,
    DateOfBirth  DATE          NOT NULL,
    DateAdmitted DATE          NOT NULL,
    WardID       INT           NOT NULL,
    CareUnitNo   INT           NOT NULL,
    BedNo        INT           NULL,
    DoctorID     INT           NOT NULL,
    CONSTRAINT PK_Patient PRIMARY KEY (PatientNo),
    CONSTRAINT FK_Patient_Ward FOREIGN KEY (WardID) REFERENCES Ward(WardID),
    CONSTRAINT FK_Patient_CareUnit FOREIGN KEY (CareUnitNo) REFERENCES CareUnit(CareUnitNo),
    CONSTRAINT FK_Patient_Bed FOREIGN KEY (BedNo) REFERENCES Bed(BedNo),
    CONSTRAINT FK_Patient_Doctor FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID),
    CONSTRAINT UQ_Patient_Bed UNIQUE (BedNo)
);

CREATE TABLE Complaint (
    ComplaintCode  INT           NOT NULL,
    Description    VARCHAR(255)  NOT NULL,
    CONSTRAINT PK_Complaint PRIMARY KEY (ComplaintCode)
);

CREATE TABLE Treatment (
    TreatmentCode  INT           NOT NULL,
    Description    VARCHAR(255)  NOT NULL,
    CONSTRAINT PK_Treatment PRIMARY KEY (TreatmentCode)
);

CREATE TABLE MedicalHistory (
    SNO            INT   NOT NULL IDENTITY(1,1),
    PatientNo      INT   NOT NULL,
    ComplaintCode  INT   NOT NULL,
    TreatmentCode  INT   NOT NULL,
    DoctorID       INT   NOT NULL,
    DateStarted    DATE  NOT NULL,
    DateEnded      DATE  NULL,
    CONSTRAINT PK_MedicalHistory PRIMARY KEY (SNO),
    CONSTRAINT FK_MH_Patient FOREIGN KEY (PatientNo) REFERENCES Patient(PatientNo),
    CONSTRAINT FK_MH_Complaint FOREIGN KEY (ComplaintCode) REFERENCES Complaint(ComplaintCode),
    CONSTRAINT FK_MH_Treatment FOREIGN KEY (TreatmentCode) REFERENCES Treatment(TreatmentCode),
    CONSTRAINT FK_MH_Doctor FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID),
    CONSTRAINT UQ_MH_ActiveTreatment UNIQUE (PatientNo, ComplaintCode, DateStarted)
);

CREATE TABLE PrevExperience (
    SNO            INT           NOT NULL IDENTITY(1,1),
    DoctorID       INT           NOT NULL,
    FromDate       DATE          NOT NULL,
    ToDate         DATE          NULL,
    Position       VARCHAR(100)  NOT NULL,
    Establishment  VARCHAR(255)  NOT NULL,
    CONSTRAINT PK_PrevExperience PRIMARY KEY (SNO),
    CONSTRAINT FK_PE_Doctor FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID)
);

CREATE TABLE PerfGrade (
    SNO          INT           NOT NULL IDENTITY(1,1),
    DoctorID     INT           NOT NULL,
    GradeDate    DATE          NOT NULL,
    Grade        VARCHAR(10)   NOT NULL,
    CONSTRAINT PK_PerfGrade PRIMARY KEY (SNO),
    CONSTRAINT FK_PG_Doctor FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID)
);
