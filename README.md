## Modelo aplicação framework Slim

##### Stack Example
```
version: '3.7'

services:
  slim_app:  
    image: base_slim:${TAG}
    container_name: first_slim
    restart: always
    env_file:
    - stack.env
    volumes:
    - /mnt/d/Amplimed/slim_first_app/:/var/www/html
    ports:  
    - "80:80"
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
```
