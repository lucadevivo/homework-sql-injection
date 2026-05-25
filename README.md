# SQL Injection Homework - Vulnerable Medical System

Questo progetto è un'applicazione web volutamente vulnerabile creata a scopo didattico per lo studio e la pratica di tecniche di **SQL Injection**. Simula il portale di gestione di un ospedale, dove medici, pazienti e amministratori possono accedere a referti e documenti.

⚠️ **ATTENZIONE**: Questa applicazione contiene vulnerabilità di sicurezza critiche. Non deve essere utilizzata in ambienti di produzione o esposta su internet.

## 🏗️ Architettura

L'ambiente è completamente containerizzato utilizzando **Docker** e include:
- **Frontend/Backend**: Apache con PHP 8.0.
- **Database**: MariaDB 10.5.
- **Dati Iniziali**: Uno script `init.sql` che popola il database con tabelle di utenti, referti e documenti segreti.

## 🎯 Obiettivi Didattici

L'applicazione permette di testare diverse tipologie di SQL Injection:
- **In-band (Union-based)**: Estrarre dati da tabelle non autorizzate.
- **Bypass di autenticazione**: Accedere come admin o medico senza conoscere la password.
- **Data Exfiltration**: Recuperare documenti segreti tramite manipolazione delle query nei moduli di ricerca o modifica.

## 🚀 Installazione e Avvio

1. Clona il repository:
   ```bash
   git clone git@github.com:lucadevivo/homework-sql-injection.git
   cd homework-sql-injection
   ```

2. Avvia l'ambiente con Docker Compose:
   ```bash
   docker-compose up -d --build
   ```

3. Accedi all'applicazione tramite browser all'indirizzo:
   [http://localhost:8080](http://localhost:8080)

## 📂 Struttura delle Vulnerabilità

- `src/login.php`: Gestisce l'autenticazione tramite query non parametrizzate.
- `src/documenti_segreti.php`: Punto di accesso privilegiato per testare l'estrazione di dati.
- `src/gestione_utenti.php`: Funzionalità amministrative soggette a manipolazione.

## 🛠️ Tecnologie utilizzate

- Docker & Docker Compose
- PHP
- MariaDB
- HTML/CSS (interfaccia minimale)

---
Progetto realizzato per il compito di **Sicurezza Informatica**.
