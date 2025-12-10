-- 1. DROP AND RECREATE DATABASE (RESET)
DROP DATABASE IF EXISTS opus;
CREATE DATABASE opus;
USE opus;

-- 2. CREATE TABLES

CREATE TABLE State (
    idState INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL,
    PRIMARY KEY (idState)
);

CREATE TABLE City (
    idCity INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL,
    zip INT NOT NULL, 
    State_idState INT NOT NULL, 
    PRIMARY KEY (idCity),
    FOREIGN KEY (State_idState) REFERENCES State(idState)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);

CREATE TABLE Role (
    idRole INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, 
    PRIMARY KEY (idRole)
);

CREATE TABLE User (
    idUser INT NOT NULL AUTO_INCREMENT,
    phone VARCHAR(45) NOT NULL, 
    email VARCHAR(100) NOT NULL,
    pass VARCHAR(255) NOT NULL, 
    name VARCHAR(45) NOT NULL,
    last_name VARCHAR(45) NOT NULL,
    picture_path VARCHAR(225) NULL, 
    Role_idRole INT NOT NULL, 
    PRIMARY KEY (idUser),
    FOREIGN KEY (Role_idRole) REFERENCES Role(idRole)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);

CREATE TABLE Location_Type (
    idLocation_Type INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, 
    PRIMARY KEY (idLocation_Type)
);

CREATE TABLE Address (
    idAddress INT NOT NULL AUTO_INCREMENT,
    street VARCHAR(100) NULL, 
    street_number VARCHAR(45) NULL,
    City_idCity INT NOT NULL, 
    Location_Type_idLocation_Type INT NOT NULL, 
    User_idUser INT NULL, 
    PRIMARY KEY (idAddress),
    FOREIGN KEY (City_idCity) REFERENCES City(idCity)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (Location_Type_idLocation_Type) REFERENCES Location_Type(idLocation_Type)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (User_idUser) REFERENCES User(idUser)
        ON DELETE SET NULL 
        ON UPDATE CASCADE
);

CREATE TABLE Appointment_Type (
    idAppointment_Type INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL, 
    price DECIMAL(10, 2) NOT NULL, 
    duration INT NOT NULL, 
    PRIMARY KEY (idAppointment_Type)
);

CREATE TABLE Receipt ( 
    idReceipt INT NOT NULL AUTO_INCREMENT,
    datetime DATETIME NULL,
    receipt_image_path VARCHAR(225) NULL, 
    PRIMARY KEY (idReceipt)
);

CREATE TABLE Appointment_Status (
    idAppointment_Status INT NOT NULL AUTO_INCREMENT,
    status_name VARCHAR(45) NOT NULL UNIQUE, 
    PRIMARY KEY (idAppointment_Status)
);

CREATE TABLE Appointment (
    idAppointment INT NOT NULL AUTO_INCREMENT,
    datetime DATETIME NOT NULL,
    Address_idAddress INT NULL, 
    Appointment_Type_idAppointment_Type INT NOT NULL, 
    Receipt_idReceipt INT NULL UNIQUE, 
    Appointment_Status_idAppointment_Status INT NOT NULL, 
    PRIMARY KEY (idAppointment),
    FOREIGN KEY (Address_idAddress) REFERENCES Address(idAddress)
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
    FOREIGN KEY (Appointment_Type_idAppointment_Type) REFERENCES Appointment_Type(idAppointment_Type)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    FOREIGN KEY (Receipt_idReceipt) REFERENCES Receipt(idReceipt)
        ON DELETE SET NULL 
        ON UPDATE CASCADE,
	FOREIGN KEY (Appointment_Status_idAppointment_Status) REFERENCES Appointment_Status(idAppointment_Status)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);

CREATE TABLE Appointment_User (
    idAppointment_User INT NOT NULL AUTO_INCREMENT,
    Appointment_idAppointment INT NOT NULL, 
    User_idUser INT NOT NULL, 
    PRIMARY KEY (idAppointment_User),
    UNIQUE KEY unique_appointment_user (Appointment_idAppointment, User_idUser), 
    FOREIGN KEY (Appointment_idAppointment) REFERENCES Appointment(idAppointment)
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    FOREIGN KEY (User_idUser) REFERENCES User(idUser)
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

CREATE TABLE Blog_Post_Category (
    idBlog_Post_Category INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, 
    PRIMARY KEY (idBlog_Post_Category)
);

CREATE TABLE Blog_Post (
    idBlog_Post INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL, 
    contents TEXT NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(45) NOT NULL DEFAULT 'draft', 
    viewcount INT NOT NULL DEFAULT 0,
    User_idUser INT NOT NULL, 
    PRIMARY KEY (idBlog_Post),
    FOREIGN KEY (User_idUser) REFERENCES User(idUser)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);

CREATE TABLE Blog_Post_Blog_Post_Category (
    idBlog_Post_Blog_Post_Category INT NOT NULL AUTO_INCREMENT,
    Blog_Post_idBlog_Post INT NOT NULL, 
    Blog_Post_Category_idBlog_Post_Category INT NOT NULL,
    PRIMARY KEY (idBlog_Post_Blog_Post_Category),
    UNIQUE KEY unique_post_category (Blog_Post_idBlog_Post, Blog_Post_Category_idBlog_Post_Category), 
    FOREIGN KEY (Blog_Post_idBlog_Post) REFERENCES Blog_Post(idBlog_Post)
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    FOREIGN KEY (Blog_Post_Category_idBlog_Post_Category) REFERENCES Blog_Post_Category(idBlog_Post_Category)
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);
-- 3. INSERT TEST DATA

-- States
INSERT INTO State (name) VALUES 
('Republika Srpska'),
('Federacija BiH');

-- Cities
INSERT INTO City (name, zip, State_idState) VALUES 
('Banja Luka', 78000, 1),
('Sarajevo', 71000, 2);

-- Roles
INSERT INTO Role (name) VALUES 
('admin'),
('worker'), 
('user');   

-- Users
INSERT INTO User (phone, email, pass, name, last_name, Role_idRole) VALUES 
('065111222', 'admin@opusinte.com', 'hashed_secret_pass', 'Admin', 'User', 1),
('065333444', 'doctor@opusinte.com', 'hashed_worker_pass', 'Iva', 'Ivanovic', 2),
('065555666', 'client@gmail.com', 'hashed_client_pass', 'Marko', 'Markovic', 3);

-- Location Types
INSERT INTO Location_Type (name) VALUES 
('Workplace'),
('Home');

-- Addresses
INSERT INTO Address (street, street_number, City_idCity, Location_Type_idLocation_Type, User_idUser) VALUES 
('Glavna Ulica', '10A', 1, 1, NULL), 
('Sporedna Ulica', '5', 1, 2, 3);    

-- Appointment Types
INSERT INTO Appointment_Type (name, price, duration) VALUES 
('Individual Therapy', 50.00, 60),
('Couples Therapy', 80.00, 90),
('Initial Consultation', 0.00, 15);

-- Appointment Statuses
INSERT INTO Appointment_Status (status_name) VALUES 
('unconfirmed'),
('confirmed'),
('completed'),
('cancelled');

-- Appointments
INSERT INTO Appointment (datetime, Address_idAddress, Appointment_Type_idAppointment_Type, Appointment_Status_idAppointment_Status) VALUES 
('2025-10-15 14:00:00', 1, 1, 2); 

-- Link Users to Appointment 1
-- UPDATED: Removed 'role' value
INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES 
(1, 2), -- Iva (Worker/Doctor)
(1, 3); -- Marko (User/Client)

-- Blog Categories
INSERT INTO Blog_Post_Category (name) VALUES 
('Anxiety'),
('Depression'),
('Self-Care'),
('News');

-- Blog Posts
INSERT INTO Blog_Post (title, contents, date, status, viewcount, User_idUser) VALUES 
('Welcome to OpusInTe', 'We are happy to announce our new website opening...', '2025-10-01', 'published', 120, 1),
('Dealing with Autumn Blues', 'As the seasons change, our mood often changes with them...', '2025-10-05', 'published', 45, 2);

-- Link Blog Posts to Categories
INSERT INTO Blog_Post_Blog_Post_Category (Blog_Post_idBlog_Post, Blog_Post_Category_idBlog_Post_Category) VALUES 
(1, 4), 
(2, 2), 
(2, 3);