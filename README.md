# SQL Injection Homework - Sistema Ospedaliero Vulnerabile

Questo progetto è un'applicazione web sviluppata a fini didattici per illustrare e testare diverse tecniche di SQL Injection. Simula un portale ospedaliero per la gestione di medici, pazienti e documenti riservati.

**Nota Bene**: L'applicazione è deliberatamente insicura. Non deve essere utilizzata per gestire dati reali o esposta su reti pubbliche.

## Architettura del sistema

L'intero ambiente è configurato tramite Docker per facilitarne il deployment e garantire la separazione dei servizi:
- **Web Server**: Apache con PHP 8.0.
- **Database**: MariaDB 10.5.
- **Dati**: Lo script `init.sql` configura automaticamente lo schema e popola le tabelle al primo avvio.

## Scenari di test

L'applicazione è strutturata per permettere l'analisi di diverse vulnerabilità comuni:
- **Authentication Bypass**: Accesso alle aree riservate senza credenziali valide.
- **In-band SQLi (Union-based)**: Estrazione di informazioni sensibili da tabelle arbitrarie.
- **Privilege Escalation**: Accesso a funzionalità amministrative tramite la manipolazione delle query.

## Setup e configurazione

È necessario avere Docker e Docker Compose installati sul sistema.

1. Clonare il repository.
2. Avviare i container:
   ```bash
   docker-compose up -d --build
   ```
3. L'interfaccia sarà raggiungibile su `http://localhost:8080`.

## Struttura delle pagine rilevanti

- `login.php`: Esempio di gestione insicura dell'autenticazione.
- `documenti_segreti.php`: Pagina contenente dati riservati, target principale per test di estrazione.
- `gestione_utenti.php`: Interfaccia per la modifica dei privilegi e dello stato degli account.

---
Progetto realizzato per l'esame di Sicurezza Informatica.
