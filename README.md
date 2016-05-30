#Per chi ha fretta
- Scaricare il repository
- Creare due database su MySQL o MariaDB
- Caricare nei database i due dump presenti nei file TGZ
- Creare un utente e dargli il permesso di *Select* sul primo database e *Select*, *Insert*, *Update*, *Delete* sul secondo
- Rinominare i due file .ini.distrib in .ini e inserire i dati del database: nei file .ini della dir principale inserire l'URL del sito
- Proteggere, spostare o cancellare la directory **admin** (vedi sotto)

#Sorgenti e dati di hypertrek.info

Il codice originale è stato scritto molti anni fa, dove possibile e se il tempo l'ha permesso sono state apportate migliorie.

La versione pubblicata su GitHub è la 3.0.0. Da qui eventuali nuove versioni seguiranno la filosofia del [continuous delivery](https://en.wikipedia.org/wiki/Continuous_delivery).

#Struttura originale
La struttura originale del sito non era fatta per essere replicata facilmente.

La parte amministrativa di editing dei contenuti gira su un computer diverso (tipicamente quello di casa di Luigi Rosa) dal server su cui gira il sito.

Sul server ci sono due database MySQL, **db1** (contenuti del sito) e **db2** (statistiche, log errori) e due utenti configurati **utente1** (utente utilizzato dal motore di visualizzazione) e **utente2** (utente utilizzato dal sistema di contribuzione) con questi privilegi:

|             | **db1**      | **db2**   |
| ----------- |:------------:|:---------:|
| **utente1** | Select | Select, Insert, Update, Delete |
| **utente2** | Select, Insert, Update, Delete | Select, Insert, Update, Delete |

Non sono necessari altri privilegi sulle tabelle. 

La struttura è tale che anche una SQL injection a causa di un errore di programmazione può al massimo danneggiare dati statistici, ma non può alterare il contenuto delle pagine.

La sicurezza del sistema di contribuzione, che **non** risiede sul serve di pubblicazione è garantita da una access list del server http. Il server MySQL consente l'accesso ad **utente2** solamente dall'IP del sistema di contribuzione.

#admin
La cartella admin contiene il sistema di editing del sito. 

**La protezione dell'admin deve essere fatta con metodi esterni** come le access list del server http. Il sistema di contribuzione non ha una gestione utenti.
