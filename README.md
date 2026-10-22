# Conecta3D — Guia de instalação

Plataforma web local que conecta pessoas e clínicas que precisam de peças
customizadas e acessíveis (adaptações, órteses simples, ferramentas
ergonômicas) a voluntários locais com impressoras 3D.

## Pré-requisitos

- [XAMPP](https://www.apachefriends.org/) instalado (traz Apache + PHP + MySQL/MariaDB juntos)
- Nenhuma outra dependência — sem Composer, sem npm, sem internet necessária depois de instalado

## Passo 1 — Colocar os arquivos no lugar certo

Extraia (ou copie) a pasta `conecta3d` inteira para dentro de:

```
C:\xampp\htdocs\conecta3d
```

A estrutura final deve ficar assim:

```
htdocs/conecta3d/
├── index.php
├── cadastro.php
├── sair.php
├── painel.php
├── nova_demanda.php
├── demanda.php
├── conexao.php
├── topo.php
├── rodape.php
├── estilo.css
├── banco/
│   ├── criar_tabelas.sql
│   ├── dados_ficticios.sql
│   └── criar_admin.sql
└── docs/
```

## Passo 2 — Ligar Apache e MySQL

Abra o **Painel de Controle do XAMPP** e clique em **Start** nos módulos
**Apache** e **MySQL**. Os dois precisam ficar verdes. Sem isso, nada
funciona — nem o banco, nem as páginas PHP.

## Passo 3 — Criar o banco de dados

Acesse o phpMyAdmin **local** (botão "Admin" na linha do MySQL do painel
do XAMPP, ou `http://localhost/phpmyadmin`) e importe os scripts **nessa
ordem**, com o banco selecionado na lateral esquerda:

1. **`banco/criar_tabelas.sql`** — cria o banco `oficinahotrods_joaodavi_banco_projeto`
   e as 3 tabelas (`usuarios`, `demandas`, `historico_status`)
2. **`banco/dados_ficticios.sql`** — popula com usuários e demandas de teste
3. **`banco/criar_admin.sql`** — cria o usuário administrador

Se o seu banco foi criado antes da coluna `ativa` existir (erro
"Unknown column 'd.ativa'"), rode também
**`banco/migracao_001_demanda_ativa.sql`**.

Use a aba **Importar** do phpMyAdmin, escolhendo cada arquivo e clicando em
Executar.

## Passo 4 — Testar o acesso

Acesse:

```
http://localhost/conecta3d/index.php
```

Entre com qualquer uma das contas abaixo, todas com a senha `123456`
(os scripts já gravam o hash real de `password_hash()`). Se cair no
painel sem erro, está tudo funcionando.

### Contas de demonstração (senha `123456`)

| E-mail | Papel |
|---|---|
| `maria@exemplo.com` | Solicitante |
| `clinica@exemplo.com` | Clínica |
| `joao@exemplo.com` | Voluntário |
| `ana@exemplo.com` | Voluntário |
| `admin@conecta3d.com` | Administrador |

## Problemas comuns

**"Connection refused" / SQLSTATE[HY000] [2002]**
O MySQL não está rodando, ou está em outra porta. Confira no Painel de
Controle do XAMPP se o módulo está realmente verde. Se persistir, veja a
porta real em **phpMyAdmin → Variáveis → filtrar por "port"** e ajuste
em `conexao.php` (linha do DSN do PDO, parâmetro `port=`).

**"Base table or view not found" (tabela não existe)**
O banco existe mas está vazio — normalmente sinal de que os scripts SQL
foram importados no phpMyAdmin errado (confira se não há mais de uma
instância de MySQL/phpMyAdmin na máquina, ex. um remoto e um local).
Reimporte `criar_tabelas.sql` e `dados_ficticios.sql` no phpMyAdmin que o
`conexao.php` realmente usa (confirme o nome do banco e a porta com um
script de diagnóstico, se necessário).

**"E-mail ou senha inválidos" mesmo com e-mail certo**
A senha salva no banco não é um hash válido de `password_hash()` —
normalmente o banco foi importado com uma versão antiga de
`dados_ficticios.sql` ou `criar_admin.sql`. Reimporte as versões atuais
ou grave o hash na coluna `senha` pela aba SQL do phpMyAdmin.

## Estrutura de permissões por papel

| Papel | Pode ver | Pode fazer |
|---|---|---|
| Solicitante / Clínica | Só as próprias demandas | Criar demanda, cancelar |
| Voluntário | Demandas abertas + as que assumiu | Aceitar, concluir |
| Administrador | Todas as demandas, inclusive as inativas | Forçar status, cancelar, inativar e reativar |

## Apagar depois de usar

Qualquer arquivo temporário de depuração (ex. `versoes.php`) deve ser **removido da pasta** assim que não for mais
necessário — eles não pedem login e representam risco de segurança se
esquecidos no ar.
