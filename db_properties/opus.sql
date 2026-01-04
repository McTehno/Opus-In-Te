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
    pass VARCHAR(255) NULL, 
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
    duration INT NULL, -- Duration in minutes, NULL its a report/observation
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

CREATE TABLE Blog_Post_Status (
    idBlog_Post_Status INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL UNIQUE, 
    PRIMARY KEY (idBlog_Post_Status)
);

CREATE TABLE Blog_Post (
    idBlog_Post INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL, 
    contents TEXT NOT NULL,
    date DATE NOT NULL,
    viewcount INT NOT NULL DEFAULT 0,
    picture_path VARCHAR(225) NULL,
    User_idUser INT NOT NULL, 
    Blog_Post_Status_idBlog_Post_Status INT NOT NULL,
    PRIMARY KEY (idBlog_Post),
    FOREIGN KEY (User_idUser) REFERENCES User(idUser)
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
	FOREIGN KEY (Blog_Post_Status_idBlog_Post_Status) REFERENCES Blog_Post_Status(idBlog_Post_Status)
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
('Prijedor', 79000, 1),
('Sarajevo', 71000, 2);

-- Roles
INSERT INTO Role (name) VALUES 
('admin'),
('radnik'), 
('korisnik');   

-- Users
INSERT INTO User (phone, email, pass, name, last_name, picture_path, Role_idRole) VALUES 
('065111222', 'admin@opusinte.com', '$2y$10$OO1uzp5XBHcIE586kEIieOdLQcgULd8s3lopiGCj5VOlCc7iJ3tKq', 'Admin', 'User', NULL, 1), -- pass: secret_pass
('065333444', 'doctor@opusinte.com', '$2y$10$uPPdcplBOSA0Lvax./T/YeUFZrvKa7T.5cQ9z.0iAAA/iTgWT//3q', 'Vanja', 'Dejanovic', "img/vanjapic/vanja_profile_transparent.png", 2), -- pass: worker_pass
('065555666', 'client@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Marko', 'Markovic', NULL, 3); -- pass: client_pass

-- More workers
INSERT INTO User (phone, email, pass, name, last_name, picture_path, Role_idRole) VALUES 
('065222333', 'mihajlo@opusinte.com', '$2y$10$mQT1yiqr4sLtOixsbbr98OFqonow2cvVDJtFih/tFPY4RpW1UGoIe', 'Mihajlo', 'Dekić', 'img/workerpic/mihajlodejanovic/profile.jpeg', 2), -- pass: pass_123
('065777888', 'elena@opusinte.com', '$2y$10$mQT1yiqr4sLtOixsbbr98OFqonow2cvVDJtFih/tFPY4RpW1UGoIe', 'Elena', 'Savić', NULL, 2); -- pass: pass_123

-- More clients
INSERT INTO User (phone, email, pass, name, last_name, picture_path, Role_idRole) VALUES 
('065000111', 'ana.anic@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Ana', 'Anić', NULL, 3), -- pass: client_pass
('065000112', 'stefan.s@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Stefan', 'Stanković', NULL, 3), -- pass: client_pass
('065000113', 'milica.m@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Milica', 'Marić', NULL, 3), -- pass: client_pass
('065000114', 'dragan.d@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Dragan', 'Dakić', NULL, 3), -- pass: client_pass
('065000115', 'jelena.j@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Jelena', 'Jović', NULL, 3), -- pass: client_pass
('065000116', 'nikola.n@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Nikola', 'Nikolić', NULL, 3), -- pass: client_pass
('065000117', 'sara.s@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Sara', 'Spasić', NULL, 3), -- pass: client_pass
('065000118', 'igor.i@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Igor', 'Ilić', NULL, 3), -- pass: client_pass
('065000119', 'maja.m@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Maja', 'Mandić', NULL, 3), -- pass: client_pass
('065000120', 'pavle.p@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Pavle', 'Popović', NULL, 3), -- pass: client_pass
('065000121', 'tanja.t@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Tanja', 'Tomić', NULL, 3), -- pass: client_pass
('065000122', 'vuk.v@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Vuk', 'Vuković', NULL, 3), -- pass: client_pass
('065000123', 'lara.l@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Lara', 'Lukić', NULL, 3), -- pass: client_pass
('065000124', 'dejan.d@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Dejan', 'Dujaković', NULL, 3), -- pass: client_pass
('065000125', 'nina.n@gmail.com', '$2y$10$lcPWZrfMmYDBR1hb/skPS.jAnAN3x5gCZxKcjpNBpBbi8h/1.v/XK', 'Nina', 'Nedić', NULL, 3); -- pass: client_pass

-- Location Types
INSERT INTO Location_Type (name) VALUES 
('Workplace'),
('Home');

-- Addresses
INSERT INTO Address (street, street_number, City_idCity, Location_Type_idLocation_Type, User_idUser) VALUES 
('Vidovdanska Ulica', '2', 1, 1, NULL), 
('Vožda Karađorđa', '2', 2, 1, NULL),
('Sporedna Ulica', '5', 1, 2, 3);    

-- Appointment Types
INSERT INTO Appointment_Type (name, price, duration) VALUES 
-- Psihoterapija
('Individualna psihoterapija', 100.00, 60),
('Individualna psihoterapija', 80.00, 45),
('Grupna psihoterapija', 120.00, 60),
('Online psihoterapija', 100.00, 60),
('Psihoterapijski rad sa djecom', 100.00, 60),
('Psihoterapijski rad sa djecom', 80.00, 45),

-- Psihološko Savjetovanje
('Psihološko savjetovanje', 60.00, 30),
('Psihološko savjetovanje djece', 60.00, 30),

-- Opservacije i Izvještaji
('Psihološka opservacija', 120.00, NULL),
('Psihološka opservacija djece', 100.00, NULL),
('Pisanje nalaza u pojedinačne svrhe', 50.00, NULL);

-- Appointment Statuses
INSERT INTO Appointment_Status (status_name) VALUES 
('nepotvrđeno'),
('potvrđeno'),
('završeno'),
('otkazano');

-- Appointments
INSERT INTO Appointment (datetime, Address_idAddress, Appointment_Type_idAppointment_Type, Appointment_Status_idAppointment_Status) VALUES 
('2025-10-15 14:00:00', 1, 1, 2); 

-- Link Users to Appointment 
INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES 
(1, 2), -- Vanja (Worker)
(1, 3); -- Marko (User/Client)

-- Blog Categories
INSERT INTO Blog_Post_Category (name) VALUES 
('Stres i Anksioznost'),
('Depresija'),
('Djeca i Adolescencija'),
('Lični Razvoj'),
('Roditeljstvo'),
('Psihološki Savjeti'),
('Ljubav i Veze');

-- Blog Post Statuses
INSERT INTO Blog_Post_Status (name) VALUES 
('u pripremi'),      -- ID 1
('objavljeno'),  -- ID 2
('arhivirano');   -- ID 3

-- Blog Posts
INSERT INTO Blog_Post (title, contents, date, viewcount, picture_path, User_idUser, Blog_Post_Status_idBlog_Post_Status) VALUES
('Dobrodošli u OpusInTe', 'Sa velikim zadovoljstvom vam predstavljamo OpusInTe, vaš novi centar za psihološku podršku i lični razvoj. Naša misija je pružiti sigurno i podržavajuće okruženje u kojem svako može raditi na svom mentalnom zdravlju bez osude. Bilo da se suočavate sa anksioznošću, životnim prekretnicama ili jednostavno želite bolje razumjeti sebe, naš tim stručnjaka je tu da vas sasluša i usmjeri. Lansiranjem ove web stranice želimo vam olakšati pristup informacijama, zakazivanje termina i edukativnim sadržajima. Ovdje ćete redovno pronalaziti korisne tekstove i savjete koji vam mogu pomoći u svakodnevnom životu. Vjerujemo da je briga o mentalnom zdravlju ključ sretnog života i radujemo se što ćemo biti dio vašeg putovanja ka boljem sutra.', '2025-10-01', 120, 'img/blogplaceholder/blog_placeholder_2.jpg', 1, 2),
('Kako se nositi sa jesenjom sjetom?', 'S dolaskom jeseni, kraćim danima i kišnim vremenom, mnogi od nas osjećaju pad energije i raspoloženja. Ovo stanje, često nazivano jesenja sjeta, prirodna je reakcija tijela na smanjenje sunčeve svjetlosti. Iako je potreba za povlačenjem u toplinu doma normalna, važno je ne prepustiti se potpunoj pasivnosti. Pokušajte iskoristiti svaki sunčan trenutak za šetnju na svježem zraku, jer svjetlost direktno utiče na proizvodnju serotonina, hormona sreće. Održavanje fizičke aktivnosti i uravnotežena ishrana bogata vitaminima takođe mogu pomoći u borbi protiv umora. Jesen je idealno vrijeme da prigrlite koncept udobnosti – uživajte u toplim napicima, dobrim knjigama i druženju s dragim ljudima. Ako primijetite da tuga traje predugo i ometa vaš život, to može biti znak sezonskog afektivnog poremećaja, pa je razgovor sa stručnjakom preporučljiv.', '2025-10-05', 45, 'img/blogplaceholder/blog_placeholder_3.jpeg', 2, 2),
('Kako se nositi sa svakodnevnim stresom?', 'Stres je neizbježan dio savremenog života, ali način na koji reagujemo na njega čini veliku razliku. Svakodnevni pritisci na poslu, u porodici ili finansijski izazovi mogu dovesti do hroničnog stresa koji negativno utiče na naše fizičko i mentalno zdravlje. Prvi korak u upravljanju stresom je prepoznavanje njegovih izvora. Da li je to pretrpan raspored, nedostatak sna ili konflikti u odnosima? Kada identifikujete uzroke, možete početi raditi na rješenjima. Tehnike opuštanja poput dubokog disanja, meditacije ili jednostavne šetnje u prirodi mogu značajno smanjiti nivo kortizola. Takođe, važno je postaviti granice i naučiti reći "ne" obavezama koje vas iscrpljuju. Fizička aktivnost je još jedan moćan alat u borbi protiv stresa, jer oslobađa endorfine koji poboljšavaju raspoloženje. Ne zaboravite ni na važnost socijalne podrške – razgovor sa prijateljem ili terapeutom može vam pomoći da sagledate situaciju iz druge perspektive.', '2025-10-06', 32, 'img/blogplaceholder/blog_placeholder_3.png', 2, 2),
('Prepoznavanje simptoma depresije', 'Depresija je više od prolazne tuge; to je ozbiljno stanje koje zahtijeva pažnju i liječenje. Mnogi ljudi ne prepoznaju simptome depresije dok oni ne počnu značajno ometati njihov svakodnevni život. Ključni znakovi uključuju uporan osjećaj praznine, beznađa ili tuge, gubitak interesa za aktivnosti koje su vas nekada radovale, te promjene u apetitu i spavanju. Možete se osjećati stalno umorno, imati poteškoća sa koncentracijom ili donositi odluke teže nego inače. Fizički simptomi poput neobjašnjivih bolova ili probavnih smetnji takođe mogu biti prisutni. Ako primijetite ove znakove kod sebe ili bliske osobe, važno je potražiti stručnu pomoć. Depresija se uspješno liječi psihoterapijom, a ponekad i medikamentima. Razgovor sa stručnjakom može vam pružiti alate potrebne za oporavak i povratak kvalitetnom životu. Zapamtite, traženje pomoći nije znak slabosti, već hrabrosti.', '2025-10-07', 55, 'img/blogplaceholder/blog_placeholder_4.webp', 2, 2),
('Izazovi adolescencije: Vodič za roditelje', 'Adolescencija je period intenzivnih promjena, kako za dijete, tako i za roditelje. Hormonalne promjene, potraga za identitetom i pritisak vršnjaka mogu stvoriti buru emocija kod tinejdžera. Roditelji se često osjećaju zbunjeno ili odbačeno kada se njihovo dijete počne povlačiti ili buntovno ponašati. Ključ uspješnog roditeljstva u ovom periodu je održavanje otvorene komunikacije. Slušajte svoje dijete bez osuđivanja i pokušajte razumjeti njihovu perspektivu, čak i kada se ne slažete. Postavljanje jasnih, ali fleksibilnih granica pomaže tinejdžerima da razviju osjećaj odgovornosti. Važno je pokazati im da ste tu za njih, bez obzira na sve. Ponekad je potrebno potražiti savjet stručnjaka ako primijetite drastične promjene u ponašanju, povlačenje iz društva ili pad školskog uspjeha. Zapamtite, vaša podrška i razumijevanje su im najpotrebniji upravo onda kada se čini da vas odguruju.', '2025-10-08', 41, 'img/blogplaceholder/blog_placeholder_5.jpg', 2, 2),
('Moć pozitivnog razmišljanja', 'Pozitivno razmišljanje nije samo ignorisanje problema, već konstruktivan pristup životnim izazovima. Način na koji razgovaramo sami sa sobom direktno utiče na naše emocije i ponašanje. Ako stalno kritikujete sebe ili očekujete najgore, vjerovatno ćete se osjećati anksiozno i nesigurno. S druge strane, njegovanje optimizma i zahvalnosti može poboljšati vaše mentalno blagostanje i otpornost na stres. Počnite tako što ćete primjećivati negativne misli i zamijeniti ih realističnijim i podržavajućim tvrdnjama. Umjesto "Nikada neću uspjeti", recite sebi "Ovo je izazov, ali mogu naučiti iz njega". Vođenje dnevnika zahvalnosti, gdje svakodnevno zapisujete tri stvari na kojima ste zahvalni, može preusmjeriti vaš fokus sa onoga što vam nedostaje na ono što imate. Pozitivan stav ne garantuje život bez problema, ali vam daje snagu da se s njima lakše nosite.', '2025-10-09', 28, 'img/blogplaceholder/blog_placeholder_6.jpg', 2, 2),
('Postavljanje granica u vaspitanju', 'Postavljanje granica je jedan od najvažnijih, ali i najtežih zadataka roditeljstva. Granice djeci pružaju osjećaj sigurnosti i strukture, pomažući im da razumiju šta se od njih očekuje. Mnogi roditelji se plaše da će postavljanjem granica izgubiti ljubav djeteta, ali istina je suprotna – djeca koja znaju pravila osjećaju se sigurnije i voljenije. Ključ je u dosljednosti. Ako jednom zabranite nešto, a drugi put to dozvolite, dijete će biti zbunjeno. Granice treba da budu jasne, primjerene uzrastu i sprovedene s ljubavlju, a ne s ljutnjom. Objasnite djetetu zašto određeno pravilo postoji. Na primjer, "Ne smiješ udarati druge jer to boli". Kada dijete poštuje granice, pohvalite ga. Kada ih prekrši, primijenite unaprijed dogovorene posljedice mirno i dosljedno. To uči djecu samokontroli i odgovornosti za sopstvene postupke.', '2025-10-10', 36, 'img/blogplaceholder/blog_placeholder_7.webp', 2, 2),
('Važnost sna za mentalno zdravlje', 'San je temelj našeg fizičkog i mentalnog zdravlja, a ipak ga često zanemarujemo. Nedostatak sna direktno utiče na našu sposobnost regulacije emocija, koncentraciju i donošenje odluka. Hronična neispavanost povezana je s povećanim rizikom od anksioznosti, depresije i drugih mentalnih poremećaja. Tokom sna, naš mozak obrađuje informacije, konsoliduje sjećanja i "čisti" se od toksina. Da biste poboljšali kvalitet sna, uspostavite redovnu rutinu odlaska na spavanje i buđenja. Izbjegavajte ekrane (telefone, računare, TV) barem sat vremena prije spavanja, jer plavo svjetlo ometa proizvodnju melatonina. Stvorite opuštajuću atmosferu u spavaćoj sobi – neka bude mračno, tiho i hladno. Izbjegavajte kofein i teške obroke kasno naveče. Ako se borite s nesanicom duže vrijeme, razmislite o konsultaciji sa stručnjakom, jer to može biti simptom dubljeg problema.', '2025-10-11', 49, 'img/blogplaceholder/blog_placeholder_8.jpg', 2, 2),
('Komunikacija kao ključ uspješne veze', 'Dobra komunikacija je temelj svake zdrave i dugotrajne veze. Često mislimo da komuniciramo, a zapravo samo čekamo svoj red da govorimo. Aktivno slušanje – istinsko fokusiranje na ono što partner govori, bez prekidanja i osude – ključno je za razumijevanje. Važno je izražavati svoje potrebe i osjećanja jasno, koristeći "ja" rečenice (npr. "Ja se osjećam povrijeđeno kada...") umjesto optuživanja ("Ti uvijek..."). Konflikti su neizbježni, ali način na koji ih rješavamo određuje kvalitet veze. Umjesto da težite pobjedi u svađi, težite rješenju koje je prihvatljivo za oboje. Ponekad je potrebno napraviti pauzu ako emocije postanu previše intenzivne. Ne zaboravite ni na neverbalnu komunikaciju – dodir, pogled i ton glasa često govore više od riječi. Redovno odvajanje vremena za razgovor o vašem odnosu može spriječiti nakupljanje nezadovoljstva.', '2025-10-12', 62, 'img/blogplaceholder/blog_placeholder_9.jpg', 2, 2),
('Tehnike disanja za smanjenje anksioznosti', 'Kada smo anksiozni, naše disanje postaje plitko i ubrzano, što tijelu šalje signal opasnosti i pojačava osjećaj panike. Srećom, svjesnom kontrolom disanja možemo prekinuti ovaj začarani krug i smiriti nervni sistem. Jedna od najjednostavnijih tehnika je "trbušno disanje". Stavite ruku na stomak i polako udišite kroz nos brojeći do četiri, osjećajući kako se stomak podiže. Zadržite dah na trenutak, a zatim polako izdišite kroz usta brojeći do šest. Ponavljanje ovog ciklusa nekoliko minuta može značajno smanjiti napetost. Druga popularna metoda je "4-7-8" tehnika: udišite 4 sekunde, držite dah 7 sekundi i izdišite 8 sekundi. Ove vježbe možete raditi bilo gdje – na poslu, u prevozu ili prije spavanja. Redovnim praktikovanjem, ove tehnike postaju moćan alat koji vam je uvijek pri ruci u stresnim situacijama.', '2025-10-13', 39, 'img/blogplaceholder/blog_placeholder_10.jpg', 2, 2),
('Kako izgraditi samopouzdanje?', 'Samopouzdanje nije urođena osobina, već vještina koja se može razviti i ojačati. Mnogi ljudi bore se s osjećajem manje vrijednosti, upoređujući se s drugima na društvenim mrežama ili u okruženju. Prvi korak ka većem samopouzdanju je prihvatanje sebe sa svim vrlinama i manama. Fokusirajte se na svoja postignuća, ma koliko mala izgledala. Postavljanje realnih ciljeva i njihovo ostvarivanje gradi osjećaj kompetentnosti. Takođe, važno je paziti na unutrašnji dijalog – budite ljubazni prema sebi kao što biste bili prema prijatelju. Suočavanje sa strahovima, umjesto izbjegavanja, takođe jača samopouzdanje. Svaki put kada uradite nešto čega se plašite, dokazujete sebi da ste sposobniji nego što mislite. Okružite se ljudima koji vas podržavaju i vjeruju u vas. Zapamtite, samopouzdanje dolazi iznutra i rezultat je kontinuiranog rada na sebi.', '2025-10-14', 44, 'img/blogplaceholder/blog_placeholder_2.jpg', 2, 2),
('Kvalitetno vrijeme sa djecom', 'U današnjem užurbanom svijetu, roditelji često osjećaju krivicu jer ne provode dovoljno vremena sa djecom. Međutim, kvalitet provedenog vremena važniji je od kvantiteta. Kvalitetno vrijeme znači potpunu prisutnost – bez telefona, televizije i ometanja. To može biti samo 15-20 minuta dnevno posvećenih isključivo djetetu. Zajedničke aktivnosti poput igranja društvenih igara, čitanja priče pred spavanje, kuhanja ili šetnje stvaraju duboku povezanost. Dozvolite djetetu da ponekad vodi igru i bira aktivnosti. Ovo im pokazuje da su važni i da cijenite njihova interesovanja. Razgovarajte s njima o njihovom danu, snovima i strahovima. Stvaranje porodičnih rituala, poput zajedničkog nedjeljnog doručka ili filmske večeri, gradi uspomene koje će trajati cijeli život. Ljubav se djeci najbolje pokazuje kroz pažnju i vrijeme koje im posvetimo.', '2025-10-15', 51, 'img/blogplaceholder/blog_placeholder_3.jpeg', 2, 2),
('Razlika između tuge i depresije', 'Tuga je prirodna ljudska emocija, reakcija na gubitak, razočaranje ili teške životne okolnosti. Svi se ponekad osjećamo tužno, ali to stanje obično prolazi s vremenom i ne ometa nas trajno u svakodnevnom funkcionisanju. Depresija je, s druge strane, klinički poremećaj raspoloženja koji traje duže (najmanje dvije sedmice) i značajno utiče na kvalitet života. Dok tuga dolazi u talasima i često je isprepletena s pozitivnim sjećanjima, depresija je konstantan osjećaj težine, praznine i bezvoljnosti. Kod depresije, samopouzdanje je često narušeno, a prisutan je i osjećaj krivice ili bezvrijednosti, što nije karakteristično za običnu tugu. Fizički simptomi poput nesanice ili prekomjernog spavanja, te promjene apetita, češći su kod depresije. Ako niste sigurni da li se radi o tugi ili depresiji, konsultacija sa stručnjakom može pomoći u postavljanju dijagnoze i odabiru pravog tretmana.', '2025-10-16', 33, 'img/blogplaceholder/blog_placeholder_4.webp', 2, 2),
('Uticaj društvenih mreža na mlade', 'Društvene mreže su postale neizostavan dio života mladih, donoseći sa sobom i prednosti i rizike. S jedne strane, omogućavaju povezivanje, kreativno izražavanje i pristup informacijama. S druge strane, prekomjerna upotreba može dovesti do anksioznosti, depresije i problema sa slikom o sebi. Mladi se često upoređuju s nerealnim, filtriranim prikazima tuđih života, što stvara osjećaj neadekvatnosti. Cyberbullying ili vršnjačko nasilje na internetu je ozbiljan problem koji može ostaviti duboke psihološke posljedice. Važno je da roditelji i mladi razgovaraju o sigurnosti na internetu i kritičkom razmišljanju. Ograničavanje vremena provedenog pred ekranom i podsticanje aktivnosti u stvarnom svijetu – sporta, hobija, druženja uživo – ključno je za mentalno zdravlje. Treba naučiti mlade da njihova vrijednost ne zavisi od broja lajkova ili pratilaca, već od njihovih osobina i djela.', '2025-10-17', 47, 'img/blogplaceholder/blog_placeholder_5.jpg', 2, 2),
('Kako reći "ne" bez osjećaja krivice', 'Mnogi ljudi imaju problem da kažu "ne" jer žele da udovolje drugima ili se plaše sukoba. Međutim, stalno pristajanje na tuđe zahtjeve na štetu sopstvenih potreba vodi ka iscrpljenosti i ogorčenosti. Reći "ne" je vještina postavljanja granica i brige o sebi. Kada kažete "ne" nečemu što ne želite, zapravo govorite "da" sebi i svom mentalnom zdravlju. Ne morate se pravdati ili izmišljati izgovore – jednostavno i ljubazno odbijanje je sasvim dovoljno. Na primjer: "Hvala na pozivu, ali neću moći doći" ili "Trenutno nemam kapaciteta za taj zadatak". U početku se možete osjećati krivim, ali s vremenom to postaje lakše. Pravi prijatelji i kolege će poštovati vaše granice. Zapamtite, vaša energija i vrijeme su ograničeni resursi i imate pravo da odlučujete kako ćete ih trošiti.', '2025-10-18', 29, 'img/blogplaceholder/blog_placeholder_6.jpg', 2, 2),
('Očuvanje bliskosti u dugim vezama', 'U dugim vezama, lako je upasti u rutinu i uzeti partnera zdravo za gotovo. Početna zaljubljenost s vremenom blijedi, ali je može zamijeniti dublja, zrelija ljubav ako se na njoj radi. Očuvanje bliskosti zahtijeva namjeru i trud. Redovno odvajanje vremena samo za vas dvoje, bez djece i obaveza, ključno je za održavanje povezanosti. To mogu biti "dejt večeri", zajednički hobiji ili jednostavno razgovor uz kafu. Fizička bliskost, ne samo seksualna, već i zagrljaji, poljupci i držanje za ruke, održava emocionalnu vezu. Pokažite interesovanje za partnerov unutrašnji svijet – pitajte ih o njihovom danu, snovima i strahovima. Izražavanje zahvalnosti za male stvari koje partner radi čini da se osjećaju cijenjenim. Iznenađenja i male gesta pažnje mogu razbiti monotoniju i unijeti svježinu u odnos. Ljubav je glagol, nešto što se radi svaki dan.', '2025-10-19', 58, 'img/blogplaceholder/blog_placeholder_9.jpg', 2, 2),
('Upravljanje vremenom i produktivnost', 'Osjećaj da nemamo dovoljno vremena čest je izvor stresa. Efikasno upravljanje vremenom ne znači raditi više, već raditi pametnije. Prvi korak je postavljanje prioriteta. Eisenhowerova matrica (hitno/bitno) može vam pomoći da razlučite šta zaista morate uraditi, a šta možete delegirati ili eliminisati. Planiranje dana unaprijed i razbijanje velikih zadataka na manje korake smanjuje osjećaj preopterećenosti i prokrastinaciju. Tehnika Pomodoro (25 minuta rada, 5 minuta pauze) odlična je za održavanje fokusa. Takođe, važno je prepoznati svoje "kradljivce vremena" – društvene mreže, nepotrebni sastanci ili perfekcionizam. Naučite reći "ne" obavezama koje nisu u skladu s vašim ciljevima. I ne zaboravite planirati vrijeme za odmor. Produktivnost nije maraton bez cilja, već balans između rada i obnove energije. Kada upravljate svojim vremenom, upravljate svojim životom.', '2025-10-20', 34, 'img/blogplaceholder/blog_placeholder_8.jpg', 2, 2);

-- Link Blog Posts to Categories
INSERT INTO Blog_Post_Blog_Post_Category (Blog_Post_idBlog_Post, Blog_Post_Category_idBlog_Post_Category) VALUES 
(1, 4), 
(2, 2), 
(2, 3),
(3, 1),
(4, 2),
(5, 3), (5, 5),
(6, 4),
(7, 5),
(8, 6),
(9, 7),
(10, 1), (10, 6),
(11, 4),
(12, 5), (12, 3),
(13, 2), (13, 6),
(14, 3), (14, 4),
(15, 6), (15, 4),
(16, 7),
(17, 4), (17, 1);

DELIMITER //

CREATE PROCEDURE GenerateTestAppointmentsSafe()
BEGIN
    DECLARE i INT DEFAULT 0;
    DECLARE random_worker_id INT;
    DECLARE random_client_id INT;
    DECLARE random_type_id INT;
    DECLARE random_status_id INT;
    DECLARE random_date DATETIME;
    DECLARE new_app_id INT;
    DECLARE slot_occupied INT;

    -- Increase the number of days to 15
    WHILE i < 200 DO
        -- 1. Generate random values
        SET random_date = TIMESTAMP(
            DATE_SUB(CURDATE(), INTERVAL FLOOR(RAND() * 16) DAY), -- Last 15 days + today
            MAKETIME(FLOOR(8 + RAND() * 13), 0, 0)                -- Full hour (8:00 - 20:00)
        );
        
        SET random_worker_id = ELT(FLOOR(1 + RAND() * 3), 2, 4, 5); -- Vanja, Mihajlo or Elena

        -- 2. CONFLICT CHECK: Check if this worker already has an appointment at this time
        SELECT COUNT(*) INTO slot_occupied
        FROM Appointment a
        JOIN Appointment_User au ON a.idAppointment = au.Appointment_idAppointment
        WHERE a.datetime = random_date 
        AND au.User_idUser = random_worker_id
        AND a.Appointment_Status_idAppointment_Status != 4; -- Ignore cancelled appointments

        -- 3. If the slot is free, insert data
        IF slot_occupied = 0 THEN
            SET random_type_id = FLOOR(1 + RAND() * 11);
            
            IF random_date < NOW() THEN
                SET random_status_id = IF(RAND() > 0.15, 3, 4); 
            ELSE
                SET random_status_id = IF(RAND() > 0.2, 2, 1);
            END IF;

            INSERT INTO Appointment (datetime, Address_idAddress, Appointment_Type_idAppointment_Type, Appointment_Status_idAppointment_Status)
            VALUES (random_date, IF(RAND() > 0.3, 1, 2), random_type_id, random_status_id);
            
            SET new_app_id = LAST_INSERT_ID();

            -- Link worker
            INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES (new_app_id, random_worker_id);

            -- Link random client (IDs 6-20)
            SET random_client_id = FLOOR(6 + RAND() * 15);
            INSERT INTO Appointment_User (Appointment_idAppointment, User_idUser) VALUES (new_app_id, random_client_id);

            -- Only increment counter if insertion was successful
            SET i = i + 1;
        END IF;
        
        -- If the slot was occupied, the WHILE loop will simply go to the next iteration without 'i = i + 1'
    END WHILE;
END //


DELIMITER ;
CALL GenerateTestAppointmentsSafe();
DROP PROCEDURE IF EXISTS GenerateTestAppointmentsSafe;