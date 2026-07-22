# Atualização do Módulo 07 no cPanel

## 1. Backup

Antes de atualizar:

1. Compacte `public_html/app/`.
2. Exporte o banco pelo phpMyAdmin.

## 2. Atualizar o banco

No phpMyAdmin, importe apenas:

```text
database/migrations/007_security_and_audit.sql
```

A migração cria:

```text
login_attempts
audit_logs
```

## 3. Atualizar os arquivos

1. Envie `tipsforme-module-07-patch.zip` para `public_html/app/`.
2. Extraia dentro dessa pasta.
3. Confirme a substituição dos arquivos.

O pacote não contém `.env`.

## 4. Configuração opcional do `.env`

Adicione:

```env
SESSION_IDLE_TIMEOUT=1800
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCK_MINUTES=15
```

Significado:

- `SESSION_IDLE_TIMEOUT=1800`: encerra a autenticação depois de 30 minutos sem atividade.
- `LOGIN_MAX_ATTEMPTS=5`: permite cinco falhas no período.
- `LOGIN_LOCK_MINUTES=15`: bloqueia novas tentativas por quinze minutos.

Se essas linhas não forem adicionadas, os mesmos valores serão usados como padrão.

## 5. Testes

1. Entre normalmente no sistema.
2. Abra `/audit`.
3. Crie ou edite um colaborador.
4. Volte a `/audit` e confira o registo.
5. Faça logout e login novamente.
6. Confira os eventos de entrada e saída.
7. Teste a página em janela privada com uma senha incorreta.

Não faça cinco tentativas erradas com a conta principal durante o teste. Use no máximo duas.

## 6. Produção

Mantenha:

```env
APP_ENV=production
APP_DEBUG=false
```

Apague instaladores temporários e nunca envie o `.env` ao GitHub.
