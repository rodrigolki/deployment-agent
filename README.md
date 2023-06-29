## Modelo aplicação framework Slim

##### Stack Example
```
version: '3.7'

services:
  dp-agent:  
    image: deployment_agent:debug
    container_name: dp_agent
    restart: always
    env_file:
    - stack.env
    volumes:
    - /var/deployment_agent/:/var/www/html
    ports:  
    - "5000:8080"
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "1"
```
##### Environment Variables

```
TAG= 
DD_AGENT_URL=
DD_ENV=
DD_SERVICE=
DEV=
PORTAINER_HOST=
PORTAINER_PORT=
PORTAINER_USER=
PORTAINER_PASS=
REGEX_TAG=
AUTH_SECRET=
VERSION_VARIABLE=
DISCORD_WEBHOOK=
```
* VERSION_VARIABLE define qual é a variável de ambiente da stack que especifica a versão que está sendo utilizada

* REGEX_TAG define um regex da TAG que vai ser escutada pela instância do agente de deploy

* AUTH_SECRET define um token de autenticação para o serviço, o mesmo deve ser passado como query param para as requests

* DISCORD_WEBHOOK define um webhook para disparo de mensagem ao Discord após cada atualização