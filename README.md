#Sorgenti e dati di hypertrek.info

La pubblicazione dei sorgenti e dei dati di HyperTrek non e` una mera copia di file su GitHub, ma passa per una ripulitura del codice per rendere l'installazione di HyperTrek replicabile facilmente.

Il codice originale e` stato scritto molti anni fa, sove possibile e se il tempo l'ha permesso sono state apportate migliorie.

#Struttura originale
La struttura originale del sito non era fatta per essere replicata facilmente.

La parte amministrativa di editing dei contenuti gira su un computer diverso (tipicamente quello di casa di Luigi Rosa) dal server su cui gira il sito.

Sul server ci sono due databae MySQL, **db1** (contenuti del sito) e **db2** (statistiche, log errori) e due utenti configurati **utente1** (utente utilizzato dal motore di visualizzaizone) e **utente2** (otente utilizzato dal sistema di contribuzione) con questi permessi:

|             | **db1**      | **db2**   |
| ----------- |:------------:|:---------:|
| **utente1** | Sola lettura | Scrittura |
| **utente2** | Scrittura    | Scrittura |

La struttura e` tale che anche una SQL injection a causa di un errore di programmazione puo` al massimo danneggiare dati statistici, ma non puo` alterare il contenuto delle

La sicurezza del sistema di coontribuzione, che **non** risiede sul serve di pubblicazione e` garantita da una access list del server http. Il server MySQL consente l'accesso ad **utente2** solamente dall'IP del sistema di contribuzione.