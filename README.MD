## symfony

Pour voir la liste des commandes Symfony: ``php bin/console``

## commandes

### Lancer des logs en boucle

```
Usage:
  app:submit-events [options]

Options:
      --speed[=SPEED]   slow|normal|fast [default: "slow"]
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -e, --env=ENV         The Environment name. [default: "dev"]
      --no-debug        Switch off debug mode.
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

### Générer des logs d'accès HTTP

Variables d'environnements:

```
BUSINESS_LOGGER_URL : URL complète du server qui reçoit les logs
```

```
Usage:
  app:gen-access-log [options]

Options:
      --mode[=MODE]      stream|batch [default: "batch"]
      --speed[=SPEED]    slow|normal|fast [default: "slow"]
      --output[=OUTPUT]  stdout|FILE_PATH [default: "stdout"]
  -h, --help             Display help for the given command. When no command is given display help for the list command
  -q, --quiet            Do not output any message
  -V, --version          Display this application version
      --ansi             Force ANSI output
      --no-ansi          Disable ANSI output
  -n, --no-interaction   Do not ask any interactive question
  -e, --env=ENV          The Environment name. [default: "dev"]
      --no-debug         Switch off debug mode.
  -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```


### Générer des tweets au format JSON-ND

```
Usage:
  app:tweets [options]

Options:
      --count[=COUNT]    how many tweets [default: 10]
      --output[=OUTPUT]  stdout|FILE_PATH [default: "stdout"]
  -h, --help             Display help for the given command. When no command is given display help for the list command
  -q, --quiet            Do not output any message
  -V, --version          Display this application version
      --ansi             Force ANSI output
      --no-ansi          Disable ANSI output
  -n, --no-interaction   Do not ask any interactive question
  -e, --env=ENV          The Environment name. [default: "dev"]
      --no-debug         Switch off debug mode.
  -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```