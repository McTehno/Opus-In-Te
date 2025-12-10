
-- Use the created database
USE opus;

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
        ON UPDATE CASCADE -- If the id of state is changed, so should the fk
);

CREATE TABLE Role (
    idRole INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, -- user (for normal users), worker (for employees), admin
    PRIMARY KEY (idRole)
);

CREATE TABLE User (
    idUser INT NOT NULL AUTO_INCREMENT,
    phone VARCHAR(45) NOT NULL, 
    email VARCHAR(100) NOT NULL,
    pass VARCHAR(255) NOT NULL, -- Length 255 for hashed passwords
    name VARCHAR(45) NOT NULL,
    last_name VARCHAR(45) NOT NULL,
    picture_path VARCHAR(225) NULL, -- Profile picture is optional
    Role_idRole INT NOT NULL, 
    PRIMARY KEY (idUser),
    FOREIGN KEY (Role_idRole) REFERENCES Role(idRole)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);

CREATE TABLE Location_Type (
    idLocation_Type INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, -- work (address of a workplace), personal (personal address of a worker or a user)
    PRIMARY KEY (idLocation_Type)
);

CREATE TABLE Address (
    idAddress INT NOT NULL AUTO_INCREMENT,
    street VARCHAR(100) NULL, -- Potentially nullable
    street_number VARCHAR(45) NULL,
    City_idCity INT NOT NULL, 
    Location_Type_idLocation_Type INT NOT NULL, 
    User_idUser INT NULL, -- An address might be office location not tied to a specific user initially, so User is nullable
    PRIMARY KEY (idAddress),
    FOREIGN KEY (City_idCity) REFERENCES City(idCity)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (Location_Type_idLocation_Type) REFERENCES Location_Type(idLocation_Type)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (User_idUser) REFERENCES User(idUser)
        ON DELETE SET NULL -- If user is deleted, keep address but remove user link
        ON UPDATE CASCADE
);

CREATE TABLE Appointment_Type (
    idAppointment_Type INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL, -- Could be a long name with this type of establishment 
    price DECIMAL(10, 2) NOT NULL, -- Price with 2 decimal places
    duration INT NOT NULL, -- Duration in minutes
    PRIMARY KEY (idAppointment_Type)
);

CREATE TABLE Receipt ( -- Everything is optional (except for the PK), because the receipt can be added later, but needs to connect to the appointment
    idReceipt INT NOT NULL AUTO_INCREMENT,
    datetime DATETIME NULL,
    receipt_image_path VARCHAR(225) NULL, -- Receipt image
    PRIMARY KEY (idReceipt)
);

CREATE TABLE Appointment (
    idAppointment INT NOT NULL AUTO_INCREMENT,
    datetime DATETIME NOT NULL,
    status VARCHAR(45) NOT NULL DEFAULT 'scheduled', --  scheduled, completed, cancelled
    Address_idAddress INT NULL, -- Location of the appointment (can be null for online)
    Appointment_Type_idAppointment_Type INT NOT NULL, 
    Receipt_idReceipt INT NULL UNIQUE, 
    PRIMARY KEY (idAppointment),
    FOREIGN KEY (Address_idAddress) REFERENCES Address(idAddress)
        ON DELETE SET NULL -- If address is deleted, keep appointment but remove location link
        ON UPDATE CASCADE,
    FOREIGN KEY (Appointment_Type_idAppointment_Type) REFERENCES Appointment_Type(idAppointment_Type)
        ON DELETE RESTRICT -- Prevent deleting appointment type if appointments exist
        ON UPDATE CASCADE,
    FOREIGN KEY (Receipt_idReceipt) REFERENCES Receipt(idReceipt)
        ON DELETE SET NULL -- If receipt is deleted, keep appointment but remove receipt link
        ON UPDATE CASCADE
);

CREATE TABLE Appointment_User (
    idAppointment_User INT NOT NULL AUTO_INCREMENT,
    Appointment_idAppointment INT NOT NULL, 
    User_idUser INT NOT NULL, 
    PRIMARY KEY (idAppointment_User),
    UNIQUE KEY unique_appointment_user (Appointment_idAppointment, User_idUser), -- Prevent duplicate entries for same user/appointment
    FOREIGN KEY (Appointment_idAppointment) REFERENCES Appointment(idAppointment)
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    FOREIGN KEY (User_idUser) REFERENCES User(idUser)
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

CREATE TABLE Blog_Post_Category (
    idBlog_Post_Category INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, -- Just a category of the blog post
    PRIMARY KEY (idBlog_Post_Category)
);

CREATE TABLE Blog_Post (
    idBlog_Post INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL, 
    contents TEXT NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(45) NOT NULL DEFAULT 'draft', -- draft, published, archived
    viewcount INT NOT NULL DEFAULT 0,
    User_idUser INT NOT NULL, -- Author of the post (Foreign key to User)
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
    UNIQUE KEY unique_post_category (Blog_Post_idBlog_Post, Blog_Post_Category_idBlog_Post_Category), -- Prevent linking same category multiple times to same post
    FOREIGN KEY (Blog_Post_idBlog_Post) REFERENCES Blog_Post(idBlog_Post)
        ON DELETE CASCADE 
        ON UPDATE CASCADE,
    FOREIGN KEY (Blog_Post_Category_idBlog_Post_Category) REFERENCES Blog_Post_Category(idBlog_Post_Category)
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);

