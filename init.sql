-- ==========================================
-- 1. UTENTI (Tabelle Separate con Stato)
-- ==========================================
CREATE TABLE admin (
    username VARCHAR(50) PRIMARY KEY,
    password VARCHAR(50),
    nome VARCHAR(100),
    stato VARCHAR(20) DEFAULT 'attivo' -- Nuova colonna
);

CREATE TABLE medici (
    codice_medico VARCHAR(50) PRIMARY KEY,
    password VARCHAR(50),
    nome VARCHAR(100),
    livello VARCHAR(50),
    stato VARCHAR(20) DEFAULT 'attivo' -- Nuova colonna
);

CREATE TABLE pazienti (
    codice_fiscale VARCHAR(16) PRIMARY KEY,
    password VARCHAR(50),
    nome VARCHAR(100),
    medico_assegnato VARCHAR(50),
    stato VARCHAR(20) DEFAULT 'attivo' -- Nuova colonna
);

-- ==========================================
-- 2. REFERTI PAZIENTI E DOCUMENTI SEGRETI
-- ==========================================
CREATE TABLE referti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paziente_cf VARCHAR(50),
    diagnosi TEXT,
    terapia TEXT,
    data_referto DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE documenti_segreti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(100),
    contenuto TEXT,
    livello_riservatezza VARCHAR(50),
    data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_creazione VARCHAR(50)
);
-- ==========================================
-- 2. POPOLAMENTO UTENTI 
-- ==========================================

-- ADMIN
INSERT INTO admin (username, password, nome) VALUES 
('IT_ADMIN_SYS_001', 'P@ssw0rdRootDB!', 'Amministratore IT'),
('MEDICO02DIRETTOR', 'DirezioneSanitaria26', 'Dott.ssa Lisa Cuddy');

-- MEDICI
INSERT INTO medici (codice_medico, password, nome, livello) VALUES 
('MEDICO01ADMINXXX', 'SuperSegreta123!', 'Dott. Gregory House', 'medico_senior'),
('MEDICO03CHIRURGO', 'BisturiDoro99', 'Dott. Derek Shepherd', 'medico_senior'),
('MEDICO04BASEXXXX', 'medico2025', 'Dott. Ugo Sartori', 'medico_base');

-- PAZIENTI (Assegnati ai medici)
INSERT INTO pazienti (codice_fiscale, password, nome, medico_assegnato) VALUES 
('RSSMRA80A01H501Z', 'password123', 'Mario Rossi', 'MEDICO01ADMINXXX'),
('BNCGLI90B41H501X', 'giulia90', 'Giulia Bianchi', 'MEDICO04BASEXXXX'),
('VRDLGI75C12F205Y', 'juventus75', 'Luigi Verdi', 'MEDICO03CHIRURGO'),
('NROMRA65D22G273E', 'maria1965', 'Maria Neri', 'MEDICO04BASEXXXX'),
('GLLLCA92E05H501A', 'luca.gialli', 'Luca Gialli', 'MEDICO01ADMINXXX'),
('FRRMRC88F14L219W', 'qwertyuiop', 'Marco Ferrari', 'MEDICO03CHIRURGO'),
('RMANNA82A45A089Q', 'anna82roma', 'Anna Romano', 'MEDICO01ADMINXXX'),
('CSTGNN78B12F205M', 'gianni78', 'Giovanni Costa', 'MEDICO04BASEXXXX'),
('GLLFNC95C64H501U', 'francesca95', 'Francesca Galli', 'MEDICO03CHIRURGO'),
('MNTMTT91D03G273V', 'matteo91', 'Matteo Monti', 'MEDICO04BASEXXXX'),
('MRTSRA87E48F205D', 'sara87m', 'Sara Martini', 'MEDICO01ADMINXXX'),
('BRBPLA73F19H501K', 'paolobarbieri', 'Paolo Barbieri', 'MEDICO03CHIRURGO'),
('LOMCHR85A52L219S', 'chiara85', 'Chiara Lombardi', 'MEDICO04BASEXXXX'),
('FNTGCP70B25A089T', 'giuseppe1234', 'Giuseppe Fontana', 'MEDICO01ADMINXXX'),
('CRSSMN89C55G273J', 'simone89', 'Simone Caruso', 'MEDICO03CHIRURGO'),
('MRRNDR81D10H501C', 'andrea81', 'Andrea Mariani', 'MEDICO04BASEXXXX'),
('RZZVNT94E61F205P', 'valentina94', 'Valentina Rinaldi', 'MEDICO01ADMINXXX'),
('GTTLCU83F07L219B', 'lucagatti', 'Luca Gatti', 'MEDICO03CHIRURGO'),
('BLLFRD76A28A089N', 'federico76', 'Federico Bellini', 'MEDICO04BASEXXXX'),
('PGLMRT88B49G273H', 'marta88', 'Marta Pugliese', 'MEDICO01ADMINXXX');

-- ==========================================
-- 3. POPOLAMENTO REFERTI (Tutti e 26 + Segreti, con Date)
-- ==========================================

INSERT INTO documenti_segreti (titolo, contenuto, livello_riservatezza, data_creazione, admin_creazione) VALUES 
('PASSWORD BACKUP SERVER', 'IP: 192.168.1.100 - Pass: Ospedale2026!', 'TOP SECRET', '2026-01-10 08:00:00', 'IT_ADMIN_SYS_001'),
('CODICI ACCESSO FARMACIA', 'Armadietto stupefacenti: 4489', 'CONFIDENZIALE', '2026-02-15 09:15:00', 'IT_ADMIN_SYS_001'),
('PIANO TAGLI PERSONALE 2027', 'Riduzione budget chirurgia del 15%', 'RISERVATO', '2026-04-20 14:30:00', 'MEDICO02DIRETTOR');

-- Referti Pazienti Normali
INSERT INTO referti (paziente_cf, diagnosi, terapia, data_referto) VALUES 
('RSSMRA80A01H501Z', 'Lieve influenza stagionale con rinite', 'Riposo per 5 giorni, paracetamolo al bisogno.', '2026-05-02 10:30:00'),
('RSSMRA80A01H501Z', 'Lombalgia acuta', 'Fisioterapia, ibuprofene 600mg 2 volte al giorno.', '2026-04-15 09:15:00'),
('BNCGLI90B41H501X', 'Sospetta allergia alimentare', 'Eseguire test rast per frutta a guscio, antistaminico.', '2026-03-20 11:00:00'),
('VRDLGI75C12F205Y', 'Frattura composta clavicola destra', 'Applicazione tutore a otto per 30 giorni. Ortopedico.', '2026-05-08 14:45:00'),
('NROMRA65D22G273E', 'Ipertensione arteriosa stadio 1', 'Ramipril 5mg 1 compressa al mattino, dieta iposodica.', '2025-12-10 08:30:00'),
('GLLLCA92E05H501A', 'Distorsione caviglia sinistra (grado 2)', 'Ghiaccio, riposo, stampelle per 10 giorni.', '2026-04-22 16:15:00'),
('FRRMRC88F14L219W', 'Tonsillite batterica', 'Amoxicillina + Acido Clavulanico per 7 giorni.', '2026-02-14 10:00:00'),
('RMANNA82A45A089Q', 'Emicrania con aura', 'Triptani al bisogno. Evitare stress e luci forti.', '2026-03-05 15:20:00'),
('CSTGNN78B12F205M', 'Controllo cardiologico di routine', 'ECG nella norma. Prossimo controllo tra 2 anni.', '2025-11-20 09:40:00'),
('GLLFNC95C64H501U', 'Gastrite acuta', 'Pantoprazolo 40mg per 2 settimane. Evitare caffè e spezie.', '2026-04-30 11:10:00'),
('MNTMTT91D03G273V', 'Infezione vie urinarie', 'Ciprofloxacina 500mg per 5 giorni. Bere molta acqua.', '2026-01-12 14:00:00'),
('MRTSRA87E48F205D', 'Dermatite da contatto', 'Crema cortisonica locale 2 volte al giorno.', '2026-05-05 10:50:00'),
('BRBPLA73F19H501K', 'Insonnia severa', 'Melatonina 2mg. Se non passa, visita neurologica.', '2026-02-25 09:10:00'),
('LOMCHR85A52L219S', 'Gravidanza 12esima settimana', 'Tutto nella norma. Acido folico 400mcg.', '2026-05-07 16:30:00'),
('FNTGCP70B25A089T', 'Diabete Mellito Tipo 2', 'Iniziare terapia con Metformina 500mg. Controllo glicemia.', '2025-10-18 11:45:00'),
('CRSSMN89C55G273J', 'Sindrome del tunnel carpale', 'Uso di tutore notturno, valutazione per intervento.', '2026-03-15 15:00:00'),
('MRRNDR81D10H501C', 'Otite media acuta', 'Gocce auricolari antibiotiche, antidolorifico.', '2026-04-02 08:50:00'),
('RZZVNT94E61F205P', 'Anemia sideropenica', 'Integrazione di ferro per via orale per 3 mesi.', '2026-01-28 10:20:00'),
('GTTLCU83F07L219B', 'Gastroenterite virale', 'Idratazione, fermenti lattici, dieta in bianco.', '2026-05-01 14:10:00'),
('BLLFRD76A28A089N', 'Aritmia extrasistolica', 'Holter 24h programmato per il prossimo mese.', '2026-03-10 11:30:00'),
('PGLMRT88B49G273H', 'Attacco di panico acuto', 'Prescritto ansiolitico al bisogno. Consigliato supporto psicologico.', '2026-04-18 16:45:00'),
('RSSMRA80A01H501Z', 'Esami del sangue completi', 'Colesterolo leggermente alto. Ridurre grassi animali.', '2026-05-06 08:15:00'),
('VRDLGI75C12F205Y', 'Controllo ortopedico post-frattura', 'Callo osseo in formazione. Iniziare riabilitazione.', '2026-05-09 10:00:00'),
('NROMRA65D22G273E', 'Bronchite acuta', 'Aerosol con cortisone e broncodilatatore 2 volte al dì.', '2026-02-05 14:20:00'),
('FRRMRC88F14L219W', 'Congiuntivite allergica', 'Collirio antistaminico 3 volte al giorno.', '2026-04-25 09:30:00'),
('GLLFNC95C64H501U', 'Ecografia addome completo', 'Lieve steatosi epatica. Colecisti alitiasica. Nulla di anomalo.', '2026-05-04 15:40:00');