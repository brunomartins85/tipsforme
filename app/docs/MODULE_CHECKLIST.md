# Checklist — Módulo 07

## Segurança

- [x] Limitar tentativas de login por e-mail e IP.
- [x] Expirar autenticação por inatividade.
- [x] Renovar periodicamente o ID da sessão.
- [x] Ativar cookies de sessão `HttpOnly`, `Secure` e `SameSite=Lax`.
- [x] Adicionar cabeçalhos contra iframe e interpretação incorreta de conteúdo.
- [x] Evitar cache de páginas autenticadas.
- [x] Manter mensagens de login sem revelar se a conta existe.

## Auditoria

- [x] Registar login e logout.
- [x] Registar alterações em colaboradores.
- [x] Registar alterações em turnos.
- [x] Registar criação, edição e exclusão de lançamentos.
- [x] Registar fechamentos.
- [x] Registar alterações nas configurações.
- [x] Registar redefinições de senha sem guardar senhas ou tokens.
- [x] Criar tela de consulta com filtro por ação.
- [x] Manter isolamento por `restaurant_id`.

## Interface

- [x] Adicionar Auditoria à sidebar.
- [x] Criar linha do tempo responsiva.
- [x] Disponibilizar traduções PT/EN.
- [x] Atualizar versão dos arquivos CSS e JS.

## Validação

- [x] Validar sintaxe de todos os arquivos PHP.
- [x] Confirmar presença do pacote completo de fechamentos.
- [x] Confirmar que o patch não contém `.env`.
- [ ] Importar a migração no cPanel.
- [ ] Testar o limite de login no MySQL real.
- [ ] Testar o tempo de inatividade no servidor.
- [ ] Conferir a auditoria depois de cada operação principal.
