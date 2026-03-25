# 🐳 Guia de Configuração Docker - Chat PHP

Este documento contém instruções completas para executar a aplicação Chat PHP usando Docker.

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- [Docker](https://docs.docker.com/get-docker/) (versão 20.10 ou superior)
- [Docker Compose](https://docs.docker.com/compose/install/) (versão 1.29 ou superior)

## 🏗️ Arquitetura dos Containers

A aplicação é composta por 3 serviços Docker:

1. **php-fpm**: Container com PHP 8.2-FPM e todas as extensões necessárias
2. **nginx**: Servidor web que serve a aplicação na porta 9090
3. **adminer**: Interface web para gerenciar o banco de dados SQLite na porta 9091

## 🚀 Como Subir os Containers

### 1. Construir e Iniciar os Containers

No diretório raiz do projeto (`chat-php/`), execute:

```bash
docker-compose up -d --build
```

**O que este comando faz:**
- `up`: Inicia os containers
- `-d`: Executa em modo detached (em background)
- `--build`: Constrói as imagens antes de iniciar

### 2. Verificar se os Containers Estão Rodando

```bash
docker-compose ps
```

Você deverá ver 3 containers em execução:
- `chat-php-fpm`
- `chat-nginx`
- `chat-adminer`

### 3. Verificar os Logs (Opcional)

Para ver os logs de todos os serviços:

```bash
docker-compose logs -f
```

Para ver logs de um serviço específico:

```bash
docker-compose logs -f php-fpm
docker-compose logs -f nginx
docker-compose logs -f adminer
```

Pressione `Ctrl+C` para sair da visualização de logs.

## 🌐 Acessar a Aplicação

### Aplicação Chat PHP

Após os containers estarem rodando, acesse:

```
http://localhost:9090
```

Ou usando o IP da sua máquina:

```
http://<seu-ip>:9090
```

**Funcionalidades disponíveis:**
- Login/Autenticação
- Chat em tempo real
- Envio de mensagens
- Visualização de usuários online
- Limpeza de mensagens

## 🗄️ Acessar o Adminer (Gerenciador de Banco de Dados)

### 1. Acesse o Adminer

Abra no navegador:

```
http://localhost:9091
```

### 2. Conectar ao Banco de Dados SQLite

Na tela de login do Adminer, preencha:

| Campo | Valor |
|-------|-------|
| **Sistema** | SQLite 3 |
| **Servidor** | `/var/www/html/app/db/db.sqlite` |
| **Usuário** | *(deixe em branco)* |
| **Senha** | *(deixe em branco)* |
| **Base de dados** | *(deixe em branco)* |

**⚠️ Importante:** O caminho completo do banco deve ser exatamente:
```
/var/www/html/app/db/db.sqlite
```

### 3. Navegando no Banco de Dados

Após conectar, você poderá:
- Visualizar as tabelas: `chat_users` e `chat_messages`
- Executar queries SQL
- Ver os dados em tempo real
- Exportar/importar dados
- Modificar estruturas de tabelas

## 🛠️ Comandos Úteis

### Parar os Containers

```bash
docker-compose stop
```

Este comando para os containers mas mantém os dados.

### Parar e Remover os Containers

```bash
docker-compose down
```

**⚠️ Atenção:** Isso remove os containers, mas **preserva** o volume do banco de dados.

### Parar e Remover TUDO (incluindo volumes)

```bash
docker-compose down -v
```

**⚠️ CUIDADO:** Este comando apaga **TODOS** os dados do banco de dados!

### Reiniciar os Containers

```bash
docker-compose restart
```

Ou reiniciar um serviço específico:

```bash
docker-compose restart php-fpm
docker-compose restart nginx
docker-compose restart adminer
```

### Reconstruir as Imagens

Se você modificou o Dockerfile ou código PHP:

```bash
docker-compose up -d --build --force-recreate
```

### Acessar o Terminal de um Container

**PHP-FPM:**
```bash
docker-compose exec php-fpm bash
```

**Nginx:**
```bash
docker-compose exec nginx sh
```

**Adminer:**
```bash
docker-compose exec adminer sh
```

### Ver Uso de Recursos

```bash
docker stats
```

## 📂 Estrutura de Volumes

O projeto utiliza volumes Docker para persistência de dados:

- **Volume `db-data`**: Armazena o banco de dados SQLite
  - Compartilhado entre `php-fpm` e `adminer`
  - Localização: `/var/www/html/app/db`
  - Persiste mesmo após `docker-compose down`

## 🔧 Troubleshooting (Solução de Problemas)

### Problema: Erro "Permission Denied" no banco de dados

**Solução:**
```bash
docker-compose exec php-fpm chmod -R 777 /var/www/html/app/db
```

### Problema: Porta 9090 ou 9091 já está em uso

**Solução:** Edite o arquivo `docker-compose.yml` e altere as portas:

```yaml
ports:
  - "8080:80"  # Altere 9090 para outra porta
```

### Problema: Container não inicia

**Verificar logs:**
```bash
docker-compose logs php-fpm
```

**Reconstruir sem cache:**
```bash
docker-compose build --no-cache
docker-compose up -d
```

### Problema: Banco de dados não é criado

**Solução:** Entre no container e crie manualmente:
```bash
docker-compose exec php-fpm bash
mkdir -p /var/www/html/app/db
chmod -R 777 /var/www/html/app/db
exit
```

Depois acesse a aplicação no navegador para criar as tabelas automaticamente.

### Problema: Adminer não conecta ao banco

**Verifique se o arquivo existe:**
```bash
docker-compose exec adminer ls -la /var/www/html/app/db/
```

Se não existir, acesse a aplicação principal primeiro (http://localhost:9090) para criar o banco.

## 🔄 Backup e Restore do Banco de Dados

### Fazer Backup

```bash
# Copiar banco de dados do container para o host
docker-compose exec php-fpm cat /var/www/html/app/db/db.sqlite > backup_$(date +%Y%m%d_%H%M%S).sqlite
```

Ou usando docker cp:
```bash
docker cp chat-php-fpm:/var/www/html/app/db/db.sqlite ./backup.sqlite
```

### Restaurar Backup

```bash
docker cp ./backup.sqlite chat-php-fpm:/var/www/html/app/db/db.sqlite
docker-compose exec php-fpm chmod 666 /var/www/html/app/db/db.sqlite
```

## 📊 Monitoramento

### Ver todos os containers e seu status

```bash
docker-compose ps -a
```

### Ver uso de espaço em disco

```bash
docker system df
```

### Limpar recursos não utilizados

```bash
# Remover containers parados
docker container prune

# Remover imagens não utilizadas
docker image prune

# Remover tudo não utilizado
docker system prune -a
```

## 🌐 Acesso Remoto

Para acessar a aplicação de outra máquina na mesma rede:

1. Descubra o IP da sua máquina:
   ```bash
   ip addr show  # Linux
   ipconfig      # Windows
   ```

2. Acesse de outra máquina:
   ```
   http://<IP-DA-MAQUINA>:9090  # Aplicação
   http://<IP-DA-MAQUINA>:9091  # Adminer
   ```

## ⚙️ Variáveis de Ambiente

Para configurar variáveis de ambiente, crie um arquivo `.env` na raiz do projeto:

```env
# Exemplo de .env
PHP_VERSION=8.2
APP_PORT=9090
ADMINER_PORT=9091
```

E ajuste o `docker-compose.yml` para usar essas variáveis.

## 📝 Notas Importantes

1. **Primeiro Acesso**: Na primeira execução, o banco de dados será criado automaticamente ao acessar a aplicação
2. **Persistência**: Os dados do banco são mantidos no volume `db-data` mesmo após parar os containers
3. **Performance**: Em ambiente de desenvolvimento, o volume monta o código diretamente, permitindo hot-reload
4. **Segurança**: Em produção, use senhas e configurações mais seguras para o Adminer
5. **Tema do Adminer**: Configurado com tema "dracula" para melhor visualização

## 🆘 Suporte

Se encontrar problemas:

1. Verifique os logs: `docker-compose logs -f`
2. Verifique se as portas estão livres: `netstat -tlnp | grep -E '9090|9091'`
3. Reconstrua os containers: `docker-compose up -d --build --force-recreate`
4. Consulte a documentação oficial do Docker

## 📚 Recursos Adicionais

- [Documentação Docker](https://docs.docker.com/)
- [Documentação Docker Compose](https://docs.docker.com/compose/)
- [Documentação PHP-FPM](https://www.php.net/manual/en/install.fpm.php)
- [Documentação Nginx](https://nginx.org/en/docs/)
- [Documentação Adminer](https://www.adminer.org/)

---

**Desenvolvido com ❤️ para facilitar o desenvolvimento local do Chat PHP**
