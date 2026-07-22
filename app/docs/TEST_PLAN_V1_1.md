# Plano de testes v1.1.0

## Cadastro empresarial

1. Abra `/register` em janela anónima.
2. Tente enviar sem preencher campos.
3. Confirme que aparecem mensagens de validação.
4. Cadastre uma empresa com e-mail novo.
5. Confirme que o mesmo e-mail não pode ser cadastrado outra vez.
6. Abra o link recebido por e-mail.
7. Confirme que o link não funciona uma segunda vez.

## Onboarding

1. Faça login na empresa nova.
2. Confirme que `/onboarding` abre antes do dashboard.
3. Defina taxa, dia, idioma e fuso.
4. Finalize e confira o dashboard.
5. Faça logout e login novamente: o onboarding não deve reaparecer.

## Multitenant

1. Na empresa nova, cadastre um colaborador e um turno.
2. Entre na empresa antiga.
3. Confirme que os dados da empresa nova não aparecem.

## Recuperação e confirmação

1. Use `/verify-email/resend` para uma conta pendente.
2. Use `/forgot-password` para uma conta confirmada.
3. Confirme que tokens expirados ou usados são recusados.

## Documentos e RGPD

1. Abra os quatro documentos em PT.
2. Troque para EN e repita.
3. Autenticado, abra `/data-rights/request`.
4. Registe um pedido de exportação.
5. Confirme a entrada na tabela `data_requests`.

## Apoio ao projeto

1. Abra o item neon na sidebar.
2. Copie o IBAN.
3. Abra PayPal e Stripe em novas abas.
4. Teste em telemóvel.

## Regressão financeira

1. Cadastre turnos e lançamentos.
2. Confirme a taxa do multibanco.
3. Verifique que o dinheiro é pago na quinzena correta.
4. Verifique que o multibanco entra apenas no fechamento mensal.
5. Exporte CSV e PDF.
