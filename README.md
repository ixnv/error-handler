# error-handler

в parameters.yml надо добавить:

```yml
    graylog_logging:
      enabled: true
      host: amqp-host
      login: amqp-login
      password: amqp-password
      port: amqp-port
      source: elama #источник (название сервиса/проекта)
      environment: production
```
