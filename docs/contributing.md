# Siad Pdf Compressor: come contribuire

Siad Pdf Compressor è un progetto Open Source, a cui si può contribuire liberamente, con proposte, bugfix, 
implementazione di nuove funzionalità, correzione e miglioramento della documentazione e quant'altro.
Lo sviluppo avviene attorno al nostro repository su Github: [https://github.com/cristianoc72/siad-pdf-compressor](https://github.com/cristianoc72/siad-pdf-compressor).

## Workflow

_Prerequisito_: saper programmare in [PHP](https://www.php.net/) (versione 8.2 e successive) e avere familiatità con la piattaforma [Github](https://docs.github.com/en/get-started/quickstart) 

1. Crea un Fork, clona il repository ed applica le tue patches.
2. Esegui la test suite tramite il comando `composer test` e correggi gli eventuali errori.
3. Esegui il nostro tool di analisi statica [Psalm](https://psalm.dev/), tramite il comando `composer analytics` e correggi gli eventuali errori.
4. Verifica che il coding standard sia correto, tramite il comando `composer cs:fix`.

!!! note "Tip"
    Abbiamo creato un comando di nome __check__ per eseguire in un sol colpo sia la test suite che il tool di analisi statica e la correzione del coding standard, necessari per un'eventuale pull request.
    Esegui `composer check`

## Eseguire la Test Suite

Quando si sviluppa un'applicazione, la fase di testing è molto importante: quando applichi una patch a del codice esistente, la test suite deve girare senza errori e, se aggiungi una nuova funzionalità, non verrà presa in considerazione se non avrai scritto i test che ne comprovano il funzionamento.

Il nostro tool per il testing è [Pest](https://pestphp.com/) e forniamo uno script per lanciarlo:

```bash
composer test
```

Dato che il nostro script lancia l'eseguibile di pest, puoi passargli tutte le opzioni ed i parametri nativi di pest stesso, tramite l'operatore `--`, per esempio:

```bash
composer test -- --stop-on-failure
```

Se non vuoi usare il nostro script, puoi lanciare direttamente pest:

```
vendor/bin/pest
```

## Code Coverage

Puoi generare un code coverage report tramite i seguenti comandi:

1. `composer coverage` che genera un code coverage report e lo stampa a video.  
2. `composer coverage:html` che genera un code coverage report nel formato _html_ , nella directory `coverage/`.
3.  `composer coverage:clover` che genera un report nel formato _xml_, e lo salva in un file dal nome `clover.xml`.

## Analisi statica

Per prevenire il maggior numero di errori possibile, usiamo [Psalm](https://psalm.dev/) come tool di analisi statica.
Per lanciarlo, esegui il seguente comando:

```bash
composer analytics
```

Dopo la sua analisi, Psalm mostra gli errori e i problemi che ha scoperto ed i sui suggerimenti per correggerli.
Gli errori sono più importanti e, in genere, più dannosi, comunque dovresti correggerli entrambi.


## Coding Standard

Ti forniamo un comando per correggere facilmente eventuali errori di coding standard, tramite il tool [php-cs-fixer](https://cs.symfony.com/):

```bash
composer cs:fix
```
e per mostrare gli errori senza correggerli:
```bash
composer cs:check
```

Il coding standard di questo progetto è definito nel seguente repository [https://github.com/susina/coding-standard](https://github.com/susina/coding-standard).


## Contribuire alla Documentazione

La documentazione di siad-pdf-compressor risiede nella directory `docs/`. Tale documentazione è scritta in [markdown](https://daringfireball.net/projects/markdown/)
ed è generata tramite [MkDocs](https://www.mkdocs.org).

### Installazione dei tool

Se vuoi contribuire alla documentazione, dovresti installare i seguenti tool, per generare la doumentazione localmente:

1. [Installa MkDocs](https://www.mkdocs.org/#installation)
2. Installa il tema [Material for Mkdocs](https://squidfunk.github.io/mkdocs-material/) lanciando il comando: `pip install mkdocs-material`
