# Motor Genesis - Documentação Completa

## Visão Geral

Motor Genesis é um sistema de gerenciamento de inventário para fabricante de peças e motocicletas. O sistema oferece funcionalidades completas para controle de estoque, produção, logística e administração de usuários.

---

## 1. Requisitos do Sistema

### Software Necessário:
- **XAMPP** (Apache, MySQL, PHP)
- **PHP 7.0+** com suporte para mysqli
- **MySQL 5.7+**
- Navegador web moderno (Chrome, Firefox, Edge, Safari)

### Configuração Recomendada:
- **Servidor Web**: Apache 2.4+
- **Banco de Dados**: MySQL 8.0+ ou MariaDB
- **PHP**: 7.4+

---

## 2. Instalação e Setup

### Passo 1: Clonar/Copiar Projeto
```bash
# Extrair os arquivos para:
C:\xampp\htdocs\motor-genesis\
```

### Passo 2: Configurar Banco de Dados

#### 2.1 Criar Banco de Dados
Abra o phpMyAdmin (http://localhost/phpmyadmin) e execute:

```sql
CREATE DATABASE montadora;
USE montadora;
```

#### 2.2 Criar Tabelas

**Tabela de Usuários:**
```sql
CREATE TABLE Usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    matricula VARCHAR(20) UNIQUE,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('ADMIN', 'GERENTE', 'OPERADOR') DEFAULT 'OPERADOR',
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Tabela de Peças:**
```sql
CREATE TABLE Pecas (
    id_peca INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    quantidade INT DEFAULT 0,
    custo_unitario DECIMAL(10,2),
    preco_venda DECIMAL(10,2),
    categoria VARCHAR(50),
    status ENUM('ATIVA', 'INATIVA') DEFAULT 'ATIVA',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Tabela de Modelos:**
```sql
CREATE TABLE Modelos (
    id_modelo INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    ano_modelo INT,
    cilindrada INT,
    tipo VARCHAR(50),
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Tabela de Ordens de Produção:**
```sql
CREATE TABLE OrdensProducao (
    id_ordem INT PRIMARY KEY AUTO_INCREMENT,
    numero_ordem VARCHAR(50) UNIQUE NOT NULL,
    id_modelo INT NOT NULL,
    quantidade INT,
    status ENUM('PLANEJADA', 'PRODUZINDO', 'CONCLUIDA', 'CANCELADA') DEFAULT 'PLANEJADA',
    data_inicio DATE,
    data_conclusao DATE,
    FOREIGN KEY (id_modelo) REFERENCES Modelos(id_modelo)
);
```

**Tabela de Transportadoras:**
```sql
CREATE TABLE Transportadoras (
    id_transportadora INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(20),
    tipo VARCHAR(50),
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Tabela de Envios:**
```sql
CREATE TABLE Envios (
    id_envio INT PRIMARY KEY AUTO_INCREMENT,
    codigo_moto VARCHAR(50),
    tipo ENUM('MOTO', 'PECAS'),
    id_transportadora INT NOT NULL,
    destino VARCHAR(200),
    previsao_entrega DATE,
    status ENUM('PENDENTE', 'A CAMINHO', 'ENTREGUE', 'ATRASADO') DEFAULT 'PENDENTE',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_inicio DATETIME,
    data_fim DATETIME,
    FOREIGN KEY (id_transportadora) REFERENCES Transportadoras(id_transportadora)
);
```

**Tabela de Funcionários:**
```sql
CREATE TABLE Funcionarios (
    id_funcionario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    departamento VARCHAR(50),
    cargo VARCHAR(50),
    status ENUM('ATIVO', 'INATIVO') DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Passo 3: Configurar Conexão com Banco de Dados

Edite `conexao.php`:

```php
$host = "localhost";
$user = "root";
$pass = "sua_senha_aqui";  // Adicione sua senha do MySQL
$db = "montadora";
```

### Passo 4: Criar Usuário Administrador

Acesse phpMyAdmin e insira um usuário:

```sql
INSERT INTO Usuarios (nome, email, matricula, senha, perfil, status) 
VALUES ('Admin', 'admin@motorgenesis.com', 'ADM001', SHA2('senha123', 256), 'ADMIN', 'ATIVO');
```

---

## 3. Funcionalidades Disponíveis

### 3.1 Autenticação (index.php, cadastro.php, recuperar.php)
- Login com email ou matrícula
- Cadastro de novo usuário
- Recuperação de senha
- Logout seguro

### 3.2 Dashboard (dashboard.php)
- Visualização de KPIs (Indicadores Chave de Desempenho)
- Valor total do estoque
- Peças críticas (baixa quantidade)
- Total de funcionários
- Usuários ativos no sistema

### 3.3 Estoque (estoque.php)
- CRUD de peças
- Visualização de inventário
- Status de peças (ativa/inativa)
- Custo e preço de venda
- Filtros e buscas

### 3.4 Produção (producao.php)
- Gerenciamento de modelos de motos
- Criação de ordens de produção
- Rastreamento de status de produção
- Tabelas de modelos, OP's em produção, OP's concluídas

### 3.5 Logística (logistica.php)
- Cadastro de transportadoras
- Gerenciamento de envios
- Rastreamento de status (Pendente, A Caminho, Entregue, Atrasado)
- Indicadores de desempenho de transportadoras
- Histórico de entregas

---

## 4. Estrutura de Diretórios

```
motor-genesis/
├── index.php              # Página de login
├── cadastro.php           # Registro de novo usuário
├── recuperar.php          # Recuperação de senha
├── dashboard.php          # Painel executivo
├── estoque.php            # Gerenciamento de estoque
├── producao.php           # Gerenciamento de produção
├── logistica.php          # Gerenciamento de logística
├── conexao.php            # Conexão com banco de dados
├── seguranca.php          # Verificação de sessão/autenticação
├── proteger.php           # Proteção de páginas
├── logout.php             # Encerramento de sessão
├── css/
│   ├── style.css          # Estilos principais
│   ├── cadastro.css       # Estilos de cadastro/login
│   ├── dashboard.css      # Estilos do dashboard
│   ├── estoque.css        # Estilos do estoque
│   ├── producao.css       # Estilos da produção
│   ├── logistica.css      # Estilos da logística
│   └── senha.css          # Estilos de recuperação de senha
├── img/
│   └── logo.png           # Logomarca do sistema
├── Banco de Dados/        # Scripts e documentação do BD
├── README.md              # Leia-me
└── DOCUMENTACAO.md        # Esta documentação
```

---

## 5. Uso do Sistema

### 5.1 Primeiro Acesso
1. Acesse: http://localhost/motor-genesis/
2. Use as credenciais de admin criadas
3. Você será redirecionado ao Dashboard

### 5.2 Fluxo de Funcionamento

**DASHBOARD:**
- Visualize resumo executivo do sistema
- Acesse rapidamente os módulos principais

**ESTOQUE:**
- Cadastre novas peças
- Consulte quantidade disponível
- Monitore valores de estoque

**PRODUÇÃO:**
- Registre modelos de motocicletas
- Crie ordens de produção
- Acompanhe andamento

**LOGÍSTICA:**
- Cadastre transportadoras
- Registre novos envios
- Rastreie status de envios
- Marque entregas como finalizadas

---

## 6. Recursos de Segurança

✅ **Implementados:**
- Autenticação via sessão
- Proteção contra SQL Injection (prepared statements)
- Proteção contra XSS (htmlspecialchars)
- Senhas criptografadas (SHA2)
- Validação de entrada de dados
- Logout seguro com destruição de sessão

⚠️ **Recomendações Adicionais:**
- Use HTTPS em produção
- Mantenha PHP e MySQL atualizados
- Faça backup regular do banco de dados
- Configure firewall e controle de acesso

---

## 7. Troubleshooting

### Problema: "Erro na conexão: Access denied for user 'root'@'localhost'"

**Solução:**
1. Verifique se MySQL está rodando
2. Confirme a senha em `conexao.php`
3. Se não definiu senha no MySQL, deixe `$pass = ""`
4. Acesse phpMyAdmin para testar conexão

### Problema: "Erro 404 - Página não encontrada"

**Solução:**
1. Verifique se a pasta está em `C:\xampp\htdocs\motor-genesis\`
2. Confirme que Apache está iniciado
3. Limpe cache do navegador

### Problema: "Sessão expirada"

**Solução:**
1. Faça login novamente
2. Verifique configuração de timeout em `seguranca.php`
3. Cookies devem estar habilitados

### Problema: Formulários não enviam

**Solução:**
1. JavaScript deve estar habilitado
2. Verifique console do navegador foi erros
3. Confirme que o servidor PHP está rodando

---

## 8. Backup e Manutenção

### Backup do Banco de Dados

Via phpMyAdmin:
1. Acesse phpMyAdmin
2. Selecione banco "montadora"
3. Clique em "Exportar"
4. Escolha formato SQL
5. Clique em "Go" para baixar

Via Terminal (MySQL Dump):
```bash
mysqldump -u root -p montadora > backup_montadora.sql
```

### Restaurar Backup

```bash
mysql -u root -p montadora < backup_montadora.sql
```

---

## 9. Performance e Otimização

- Índices criados nas chaves primárias e estrangeiras
- Prepared statements reduzem overhead
- Uso de LEFT JOIN para dados relacionados
- Paginação implementada nas tabelas (em desenvolvimentos futuros)

---

## 10. Contato e Suporte

Para dúvidas ou problemas:
- Consulte esta documentação
- Verifique os arquivos de configuração
- Revise os logs do evento do servidor

---

## Versão
**Motor Genesis v1.0** - Sistema de Gerenciamento de Fabricante de Motocicletas

**Última Atualização:** 2024

---
