# Atualizar para o Módulo 02 no cPanel

## 1. Faça backup

Antes de atualizar:

- compacte a pasta `public_html/app/`;
- exporte o banco atual pelo phpMyAdmin.

## 2. Atualize o banco

No phpMyAdmin:

1. Selecione o banco do TipsForMe.
2. Clique em **Importar**.
3. Selecione:

```text
database/migrations/002_employees_and_shifts.sql
```

4. Execute a importação.

Devem aparecer:

```text
employees
shifts
shift_employees
```

Não importe novamente `001_initial_schema.sql`.

## 3. Atualize os arquivos

Abra no cPanel:

```text
public_html/app/
```

Envie o ZIP do patch e extraia nessa pasta. Escolha **substituir arquivos existentes**.

O patch não contém `.env`, portanto seus dados de conexão permanecem intactos.

## 4. Confirme o `.env`

A instalação atual deve manter:

```env
APP_URL=https://tipsforme.club/app/public
APP_ENV=production
APP_DEBUG=true
```

Depois dos testes, altere:

```env
APP_DEBUG=false
```

## 5. Teste nesta ordem

```text
https://tipsforme.club/app/public/dashboard
https://tipsforme.club/app/public/employees
https://tipsforme.club/app/public/shifts
```

Teste também:

1. cadastrar um colaborador;
2. editar o colaborador;
3. desativar e reativar;
4. criar um turno;
5. selecionar dois ou mais colaboradores;
6. editar o turno;
7. excluir um turno de teste;
8. mudar a interface entre PT e EN.

## Problemas comuns

### Erro informando que a tabela não existe

A migração `002_employees_and_shifts.sql` ainda não foi importada ou foi importada no banco errado.

### Erro 404 nas páginas novas

Confirme que estes arquivos foram substituídos:

```text
app/Core/Router.php
routes/web.php
```

### Erro 500

Mantenha temporariamente:

```env
APP_DEBUG=true
```

Copie a mensagem completa exibida na tela para análise.
