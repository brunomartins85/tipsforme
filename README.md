# TipsForMe

O **TipsForMe** nasceu de um problema simples e comum em restaurantes: dividir gorjetas de forma justa, clara e sem depender de contas feitas à mão.

A aplicação permite registar gorjetas em dinheiro e multibanco, identificar quem trabalhou em cada turno, calcular automaticamente o valor de cada colaborador e acompanhar pagamentos e históricos.

Mais do que um exercício técnico, este é um projeto que estou a construir para resolver um problema real que conheço de perto.

## Projeto online

- Site: https://tipsforme.club
- Aplicação: https://app.tipsforme.club

## Principais funcionalidades

- Cadastro de empresas e gestores
- Gestão de colaboradores
- Registo de turnos
- Lançamento de gorjetas em dinheiro e multibanco
- Taxa configurável sobre o multibanco
- Divisão automática entre os colaboradores presentes
- Fechamentos quinzenais e mensais
- Histórico de pagamentos
- Área individual do colaborador
- Relatórios em PDF e CSV
- Recuperação de senha e confirmação por e-mail
- Interface em português e inglês
- Estrutura multi-tenant por empresa
- Aplicação instalável como PWA

## Tecnologias

- PHP 8.2+
- MySQL ou MariaDB
- PDO
- HTML5
- CSS3
- JavaScript
- SMTP
- PWA
- MVC leve

## Estrutura

```text
.
├── landing-page/
│   ├── index.html
│   ├── script.js
│   └── styles.css
├── app/
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── docs/
│   ├── public/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── tests/
│   ├── bootstrap.php
│   └── VERSION
├── .gitignore
├── LICENSE
└── README.md
```

## Instalação

Requisitos:

- PHP 8.2+
- MySQL ou MariaDB
- PDO MySQL
- Apache com `mod_rewrite`
- SMTP

Passos principais:

```bash
cp app/.env.example app/.env
```

Depois configure o `.env`, crie o banco, execute as migrações em `app/database/migrations/` e aponte o Document Root para `app/public/`.

## Segurança

Este repositório não deve conter `.env`, senhas, tokens, chaves privadas, backups de configuração, logs, instaladores administrativos ou dados reais de clientes.

Credenciais anteriormente expostas devem ser substituídas, mesmo depois de removidas do Git.

## Estado atual

O projeto está em desenvolvimento ativo. O MVP principal está operacional e a fase atual concentra-se em testes, experiência de uso e evolução para dispositivos móveis.

## Apoie o projeto

O TipsForMe é desenvolvido de forma independente. As contribuições ajudam a manter domínio, hospedagem, testes e desenvolvimento.

### Transferência bancária

**Titular:** BRUNO CORREA MARTINS  
**IBAN:** `PT50 0018 0003 6397 7136 0205 8`

### PayPal

https://www.paypal.com/paypalme/brunocmartins85

### Stripe

https://donate.stripe.com/3cIdR872X6FA1XJh1XgQE00

As contribuições são voluntárias e não representam investimento, participação societária ou promessa de retorno financeiro.

## Autor

Desenvolvido por **Bruno Martins**, também conhecido como **Nibble**.

- E-mail: hello@tipsforme.club
- GitHub: https://github.com/brunomartins85

## Licença

MIT. Consulte [LICENSE](LICENSE).
