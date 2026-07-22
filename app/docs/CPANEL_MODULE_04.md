# Atualização do Módulo 04 no cPanel

## 1. Faça backup

Antes de alterar:

- compacte a pasta `public_html/app`;
- exporte o banco no phpMyAdmin.

## 2. Importe a migração

No phpMyAdmin, selecione o banco do TipsForMe e importe:

```text
database/migrations/004_settlements_and_payments.sql
```

Importe apenas uma vez.

Depois confirme:

- tabela `settlements`;
- tabela `settlement_payments`;
- colunas `cash_settlement_id` e `card_settlement_id` em `tip_distributions`;
- estado `partially_settled` disponível em `tip_entries.status`.

## 3. Atualize os arquivos

Envie `tipsforme-module-04-patch.zip` para:

```text
public_html/app/
```

Extraia e escolha substituir os arquivos existentes.

O patch não contém `.env`.

## 4. Teste

Abra:

```text
https://tipsforme.club/app/public/settlements
```

Teste recomendado:

1. Crie lançamentos entre os dias 1 e 15 com dinheiro e multibanco.
2. Abra o fechamento da primeira quinzena.
3. Confirme que apenas o dinheiro está incluído.
4. Registre o pagamento.
5. Verifique que o lançamento ficou parcialmente pago quando possui multibanco.
6. Crie lançamentos entre os dias 16 e o fim do mês.
7. Abra o fechamento mensal.
8. Confirme o dinheiro da segunda quinzena e todo o multibanco líquido do mês.
9. Registre o pagamento.
10. Confira o histórico e os valores individuais.

## 5. Depuração

Durante o teste:

```env
APP_DEBUG=true
```

Depois:

```env
APP_DEBUG=false
```
