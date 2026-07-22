# Atualização do Módulo 03 no cPanel

## 1. Backup

Antes de atualizar:

- compacte a pasta `public_html/app`;
- exporte o banco pelo phpMyAdmin.

## 2. Importar a nova migração

No phpMyAdmin:

1. selecione o banco do TipsForMe;
2. clique em **Importar**;
3. escolha `database/migrations/003_tip_entries_and_distributions.sql`;
4. execute a importação.

Devem aparecer:

```text
tip_entries
tip_distributions
```

Não importe novamente as migrações `001` e `002`.

## 3. Atualizar os arquivos

Envie `tipsforme-module-03-patch.zip` para:

```text
public_html/app/
```

Extraia e confirme a substituição dos arquivos existentes.

O patch não contém `.env`.

## 4. Conferir a URL

O `.env` deve continuar com:

```env
APP_URL=https://tipsforme.club/app/public
APP_DEBUG=true
```

## 5. Limpar cache do navegador

Use uma janela anônima ou faça atualização forçada:

```text
Windows: Ctrl + F5
Mac: Command + Shift + R
```

Isso é importante porque o CSS e o JavaScript foram alterados.

## 6. Teste funcional

1. Abra `/employees` e confirme que existem colaboradores ativos.
2. Crie um turno em `/shifts`.
3. Clique em **Lançar gorjetas**.
4. Informe dinheiro e multibanco.
5. Confirme a taxa de 25% ou a taxa configurada no restaurante.
6. Confirme o lançamento.
7. Abra os detalhes e confira cada colaborador.
8. Edite o lançamento e confirme o recálculo.
9. Exclua um lançamento de teste e confirme que o turno foi reaberto.
10. Teste a sidebar no computador e no telemóvel.

## 7. Produção

Quando tudo estiver funcionando:

```env
APP_DEBUG=false
```

## Erros comuns

### Tabela não encontrada

A migração `003_tip_entries_and_distributions.sql` não foi importada no banco correto.

### CSS antigo

Faça atualização forçada ou limpe o cache do navegador.

### Nenhum turno disponível

O turno precisa:

- estar aberto;
- possuir pelo menos um colaborador;
- ainda não possuir lançamento.

### O turno ficou fechado

Esse é o comportamento esperado após criar um lançamento. Para alterar participantes, exclua primeiro o lançamento; o turno será reaberto.
