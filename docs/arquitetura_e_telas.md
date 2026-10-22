# 3. Arquitetura e telas

O sistema usa só o que o XAMPP já traz: Apache, MySQL e PHP. Cada página é
um arquivo `.php` que consulta o banco e devolve HTML pronto; não há API,
JSON nem framework. Isso elimina a etapa de integração entre front-end e
back-end, porque os dois vivem no mesmo arquivo desde o primeiro dia.

| Camada | Tecnologia | Observação |
|---|---|---|
| Servidor | Apache do XAMPP | Projeto na pasta `htdocs/conecta3d` |
| Banco | MySQL/MariaDB do XAMPP, via phpMyAdmin | Banco `oficinahotrods_joaodavi_banco_projeto` |
| Lógica | PHP puro com PDO (*prepared statements*) | Sem Laravel, sem Composer |
| Interface | HTML e um único `estilo.css` | Sem Bootstrap ou Tailwind |
| JavaScript | Apenas se indispensável | Usado só em `confirm()` de ações destrutivas |

## Estrutura de pastas

```
htdocs/conecta3d/
├── index.php            login e link para cadastro
├── cadastro.php          autocadastro (solicitante, clínica, voluntário)
├── sair.php              encerra a sessão
├── painel.php             lista de demandas (filtrada por papel)
├── nova_demanda.php       formulário de nova demanda
├── demanda.php            detalhe, histórico e botões de status
├── conexao.php            conecta ao banco e inicia a sessão
├── topo.php               cabeçalho e menu comuns
├── rodape.php              rodapé comum
├── estilo.css              todo o visual
├── banco/
│   ├── criar_tabelas.sql
│   ├── dados_ficticios.sql
│   └── criar_admin.sql
└── docs/                   documentação do projeto
```

**Padrão de toda página** — o arquivo tem duas partes fixas. No topo, um
bloco PHP que inclui `conexao.php`, processa o formulário enviado (se
houver) e busca os dados no banco. Embaixo, o HTML que exibe esses dados,
delimitado pelos includes de `topo.php` e `rodape.php`. Essa divisão
define os territórios: a lógica de back-end mexe no bloco de cima, a
apresentação no de baixo.

## Login e segurança

O formulário envia e-mail e senha. O PHP busca o usuário pelo e-mail e
compara a senha digitada com o hash salvo no banco usando
`password_verify()` — a senha em si nunca é gravada nem comparada em
texto puro, apenas o hash gerado por `password_hash()` no momento do
cadastro. Se a comparação bater, o sistema grava `usuario_id`,
`usuario_nome` e `usuario_tipo` em `$_SESSION` e redireciona para o
painel; as demais páginas apenas verificam, via `exigirLogin()`, se
`usuario_id` existe na sessão.

Todas as consultas usam **PDO com parâmetros nomeados**
(`$pdo->prepare(...)->execute([...])`) em vez de concatenar valores
diretamente na *string* SQL — isso não é só para evitar que um nome com
apóstrofo (como "D'Ávila") quebre a consulta, mas para impedir SQL
Injection de fato, já que o valor nunca é interpretado como parte do
comando SQL.

## Telas (4)

| Tela | Arquivo | Quem usa | Observação |
|---|---|---|---|
| Login | `index.php` | Todos (não autenticados) | Link para `cadastro.php` |
| Painel | `painel.php` | Todos | Lista varia por papel: solicitante/clínica vê as próprias; voluntário vê abertas + as que assumiu; admin vê todas |
| Nova demanda | `nova_demanda.php` | Solicitante e clínica | Voluntário é redirecionado ao painel se tentar acessar |
| Detalhe da demanda | `demanda.php` | Todos (com permissão) | Histórico + botões de status conforme o papel; admin tem seção extra para forçar status ou excluir |

`sair.php` não tem tela: encerra a sessão (`session_destroy()`) e volta
ao login.

## Contas de demonstração

Criadas pelos scripts `dados_ficticios.sql` e `criar_admin.sql`. Todas
usam a senha `123456`, já gravada como hash de `password_hash()`:

| E-mail | Papel |
|---|---|
| `maria@exemplo.com` | Solicitante |
| `clinica@exemplo.com` | Clínica |
| `joao@exemplo.com` | Voluntário |
| `ana@exemplo.com` | Voluntário |
| `admin@conecta3d.com` | Administrador *(criado por `criar_admin.sql`)* |
