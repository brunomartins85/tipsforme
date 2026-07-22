# Atualização TipsForMe v1.0.1

Esta correção ajusta fechamentos, saldo pendente do dashboard e acesso aos relatórios.

## 1. Backup

Antes de atualizar:

1. compacte `public_html/app/`;
2. exporte o banco pelo phpMyAdmin.

## 2. Atualizar arquivos

1. envie `tipsforme-v1.0.1-patch.zip` para `public_html/app/`;
2. extraia nessa pasta;
3. confirme a substituição dos arquivos;
4. não substitua o `.env`.

## 3. Atualizar versão

No `.env`:

```env
APP_VERSION=1.0.1
APP_ENV=production
APP_DEBUG=false
```

## 4. Corrigir um fechamento de teste antecipado

Use somente caso um fechamento mensal tenha sido confirmado antes do último dia do mês.

No phpMyAdmin:

1. execute `database/maintenance/preview_premature_settlements.sql`;
2. confira se o fechamento de teste aparece;
3. execute `database/maintenance/rollback_premature_settlements.sql`.

O script remove apenas fechamentos criados antes da data permitida e devolve os valores ao estado pendente.

## 5. Limpar cache

A versão usa PWA. Faça uma recarga forçada ou limpe os dados do site:

```text
Windows: Ctrl + F5
Mac: Cmd + Shift + R
```

## 6. Testes

- O fechamento da primeira quinzena libera somente no dia configurado.
- O fechamento mensal libera somente no último dia do mês.
- Meses anteriores continuam disponíveis para regularização.
- O dashboard mostra somente valores ainda pendentes.
- A sidebar mostra **Relatórios**.
- CSV e PDF estão em `/reports`.
