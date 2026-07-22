# Atualização do Módulo 05 no cPanel

## 1. Segurança antes de começar

As senhas do banco e do e-mail não devem ser compartilhadas nem enviadas ao GitHub. Caso tenham sido expostas, troque-as antes de continuar.

No `.env`, remova também:

```env
INSTALLER_KEY=...
```

O instalador já não é necessário.

## 2. Faça backup

- Compacte `public_html/app/`.
- Exporte o banco pelo phpMyAdmin.

## 3. Atualize o banco

No phpMyAdmin, importe somente:

```text
database/migrations/005_employee_portal_and_password_reset.sql
```

A nova tabela será:

```text
password_reset_tokens
```

## 4. Atualize os arquivos

Extraia o patch dentro de:

```text
public_html/app/
```

Escolha substituir os arquivos existentes. O patch não contém `.env`.

## 5. Configure o SMTP

Adicione ao `.env`:

```env
SMTP_HOST=mail.seudominio.com
SMTP_PORT=465
SMTP_USERNAME=no-reply@seudominio.com
SMTP_PASSWORD=SENHA_NOVA_DO_EMAIL
SMTP_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=no-reply@seudominio.com
MAIL_FROM_NAME=TipsForMe
```

Mantenha:

```env
APP_URL=https://tipsforme.club/app/public
```

Durante o primeiro teste:

```env
APP_DEBUG=true
```

Depois:

```env
APP_DEBUG=false
```

## 6. Teste a recuperação de senha

Acesse:

```text
https://tipsforme.club/app/public/forgot-password
```

Informe o e-mail do administrador e confira a caixa de entrada e spam.

## 7. Teste o acesso do colaborador

1. Abra **Colaboradores**.
2. Cadastre ou edite um colaborador com e-mail válido.
3. Clique em **Criar acesso**.
4. Abra o e-mail recebido.
5. Defina uma senha.
6. Faça login com o e-mail do colaborador.

O redirecionamento esperado é:

```text
https://tipsforme.club/app/public/my/dashboard
```

## 8. Rotas do colaborador

```text
/my/dashboard
/my/statement
/my/payments
```

## Erros comuns

### O acesso foi criado, mas o e-mail não chegou

- Confira as variáveis SMTP.
- Confira spam.
- Verifique se a senha do e-mail está correta.
- Clique em **Reenviar acesso**.

### O e-mail já está em uso

No MVP, cada e-mail de login deve ser único em toda a aplicação.

### Link expirado

Clique em **Reenviar acesso** ou solicite nova recuperação de senha.
