# Atualização do Módulo 06 no cPanel

## 1. Faça backup

Antes de alterar qualquer arquivo:

1. Compacte `public_html/app/`.
2. Exporte o banco no phpMyAdmin.

## 2. Importe a migração

No phpMyAdmin, selecione o banco do TipsForMe e importe:

```text
database/migrations/006_restaurant_settings.sql
```

Importe somente a migração `006`.

Ela adiciona à tabela `restaurants`:

```text
default_language
first_half_closing_day
password_reset_enabled
```

## 3. Atualize os arquivos

1. Envie `tipsforme-module-06-patch.zip` para `public_html/app/`.
2. Extraia o arquivo nessa pasta.
3. Confirme a substituição dos arquivos existentes.

O patch não contém `.env` e não altera as credenciais do servidor.

## 4. Teste as configurações

Acesse:

```text
https://tipsforme.club/app/public/settings
```

Teste:

- alterar o nome do restaurante;
- alterar a taxa do multibanco;
- alterar o dia da primeira quinzena;
- alterar idioma e fuso horário;
- desativar e reativar recuperação de senha;
- alterar nome e e-mail do administrador;
- alterar a senha do administrador.

## 5. Teste os fechamentos

Exemplo: altere o fechamento para dia `10`.

A primeira quinzena deverá usar:

```text
01 até 10
```

O fechamento mensal deverá usar dinheiro de:

```text
11 até o último dia do mês
```

O multibanco mensal continuará considerando o mês inteiro.

Depois do teste, retorne para dia `15`, caso essa seja a regra do restaurante.

## 6. Configuração final

No `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tipsforme.club/app/public
```

Não importe novamente as migrações anteriores.
