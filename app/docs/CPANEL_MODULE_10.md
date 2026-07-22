# Módulo 10 no cPanel

## 1. Backup obrigatório

- Compacte `public_html/app/`.
- Exporte o banco pelo phpMyAdmin em formato SQL.

## 2. Importar a migração

No phpMyAdmin, selecione o banco do TipsForMe e importe apenas:

```text
database/migrations/008_company_registration_and_onboarding.sql
```

A migração:

- adiciona os dados legais à tabela `restaurants`;
- adiciona confirmação de e-mail aos utilizadores;
- cria `email_verification_tokens`;
- cria `registration_attempts`;
- cria `data_requests`;
- preserva restaurantes, utilizadores, colaboradores e lançamentos existentes.

## 3. Atualizar os arquivos

Envie `tipsforme-module-10-patch.zip` para:

```text
public_html/app/
```

Extraia diretamente nessa pasta e confirme a substituição.

O ZIP não contém `.env`.

## 4. Atualizar o `.env`

Use:

```env
APP_VERSION=1.1.0
APP_URL=https://app.tipsforme.club
APP_ENV=production
APP_DEBUG=false

REGISTRATION_MAX_ATTEMPTS=5
REGISTRATION_WINDOW_MINUTES=60
LEGAL_TERMS_VERSION=2026-07-21
LEGAL_PRIVACY_VERSION=2026-07-21
LEGAL_DOCUMENT_DATE=21/07/2026
LEGAL_CONTACT_EMAIL=tips@tipsforme.club

SUPPORT_IBAN=PT50 0018 0003 6397 7136 0205 8
SUPPORT_PAYPAL_URL=https://www.paypal.com/paypalme/brunocmartins85
SUPPORT_STRIPE_URL=https://donate.stripe.com/3cIdR872X6FA1XJh1XgQE00
```

Mantenha também a configuração SMTP já testada.

## 5. Limpar cache

- Mac: `Cmd + Shift + R`
- Windows: `Ctrl + F5`
- Se necessário, teste em janela anónima para atualizar o service worker.

## 6. Testar o cadastro

Abra:

```text
https://app.tipsforme.club/register
```

Crie uma empresa de teste usando um e-mail diferente do administrador atual.

Confirme:

1. o e-mail chega;
2. o link ativa a empresa;
3. o login abre o onboarding;
4. o onboarding abre o dashboard;
5. a empresa aparece isolada dos dados anteriores.

## 7. Testar as páginas novas

```text
https://app.tipsforme.club/support-project
https://app.tipsforme.club/legal/terms
https://app.tipsforme.club/legal/privacy
https://app.tipsforme.club/legal/cookies
https://app.tipsforme.club/legal/data-rights
```

No painel, verifique o item neon **Ajudar o projeto**.

## 8. Editar dados empresariais

Entre como gestor e abra:

```text
https://app.tipsforme.club/settings#company-settings
```

## Problemas comuns

### O cadastro dá erro 500

Confirme que a migração `008` foi importada e deixe `APP_DEBUG=true` apenas durante o diagnóstico.

### O e-mail não chega

- verifique SMTP no `.env`;
- confira spam;
- abra `/verify-email/resend`;
- depois volte `APP_DEBUG=false`.

### O link confirma, mas o login falha

Confirme no phpMyAdmin:

- `restaurants.status = active`;
- `users.email_verified_at` preenchido.

### Layout antigo

Limpe cache e service worker do domínio `app.tipsforme.club`.
