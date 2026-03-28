# Motor Genesis - Sistema de Gerenciamento de Montadora

Projeto acadêmico completo de gerenciamento de estoque, produção e logística para fabricante de motocicletas.

## 🚀 Status do Projeto

✅ **Sistema Funcional e Testado**
- Todos os arquivos PHP sem erros de sintaxe
- Banco de dados estruturado e normalizado
- Interface moderna com dark theme
- Segurança implementada
- Documentação completa

---

## 📋 Visão Geral

Motor Genesis é um sistema web completo que oferece funcionalidades integradas para:
- **Gestão de Estoque** de peças de motocicleta
- **Controle de Produção** de motocicletas
- **Gerenciamento Logístico** de envios e transportadoras
- **Administração de Usuários** com diferentes níveis de acesso

---

## ✨ Características Principais

### 1. **Dashboard Executivo**
- Visão geral do valor em estoque
- Alertas de peças críticas
- Contagem de funcionários e usuários ativos
- Interface intuitiva com cards informativos

### 2. **Gestão de Estoque**
- CRUD completo de peças
- Controle de quantidade disponível
- Rastreamento de custo unitário e preço de venda
- Categorização de peças
- Status de peça (Ativa/Inativa)

### 3. **Gerenciamento de Produção**
- Cadastro de modelos de motocicletas
- Criação e acompanhamento de Ordens de Produção
- Controle de status (Planejada, Produzindo, Concluída)
- Histórico de produções

### 4. **Logística e Entregas**
- Controle de transportadoras
- Gerenciamento de envios (Moto completa ou Peças)
- Rastreamento de status (Pendente, A Caminho, Entregue, Atrasado)
- Indicadores de desempenho de transportadoras

### 5. **Autenticação e Segurança**
- Login com email ou matrícula
- Níveis de acesso (ADMIN, GERENTE, OPERADOR)
- Recuperação de senha segura
- Controle de sessão com timeout

---

## 🎨 Design e Interface

- **Tema:** Dark Mode Profissional
- **Cores Principais:** Azul primário (#2962ff), Gradiente escuro
- **Ícones:** FontAwesome 6.0 integrado
- **Layout:** Sidebar fixa com menu lateral
- **Responsividade:** Adaptado para diferentes tamanhos de tela
- **Animações:** Transições suaves entre elementos

---

## 🔒 Segurança Implementada

✅ Autenticação por sessão PHP  
✅ Proteção contra SQL Injection (prepared statements)  
✅ Proteção contra XSS (htmlspecialchars com validação)  
✅ Senhas criptografadas com SHA2  
✅ Validação de entrada de dados  
✅ Destruição segura de sessão no logout  

---

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

| Tabela | Descrição |
|--------|-----------|
| **Usuarios** | Controle de acesso e autenticação |
| **Pecas** | Catálogo de peças de moto |
| **Modelos** | Modelos de motocicletas produzidas |
| **OrdensProducao** | Ordens de produção |
| **Transportadoras** | Transportadoras/Logísticas |
| **Envios** | Rastreamento de envios |
| **Funcionarios** | Dados de funcionários |

---

## 🚀 Instalação Rápida

### Requisitos
- XAMPP (Apache, PHP 7.0+, MySQL 5.7+)
- Navegador web moderno

### Passos

**1. Copiar Projeto:**
```
C:\xampp\htdocs\motor-genesis\
```

**2. Criar Banco de Dados:**
- Abra phpMyAdmin: http://localhost/phpmyadmin/
- Crie o banco: `CREATE DATABASE montadora;`
- Execute os scripts SQL em pasta `Banco de Dados/`

**3. Configurar Conexão (conexao.php):**
```php
$host = "localhost";
$user = "root";
$pass = "";  // Sua senha do MySQL (deixe vazio se não tiver)
$db = "montadora";
```

**4. Acessar Sistema:**
```
http://localhost/motor-genesis/
```

---

## 📂 Estrutura de Arquivos

```
motor-genesis/
├── index.php              # Página de login
├── cadastro.php           # Registro de usuários
├── recuperar.php          # Recuperação de senha
├── dashboard.php          # Painel executivo
├── estoque.php            # Gestão de estoque
├── producao.php           # Gerenciamento de produção
├── logistica.php          # Controle de logística
├── logout.php             # Encerramento de sessão
├── conexao.php            # Conexão com banco de dados
├── seguranca.php          # Verificação de autenticação
├── proteger.php           # Proteção de rotas
├── css/
│   ├── style.css          # Estilos do login
│   ├── cadastro.css       # Estilos de cadastro
│   ├── senha.css          # Estilos de recuperação
│   ├── dashboard.css      # Estilos do dashboard
│   ├── estoque.css        # Estilos do estoque
│   ├── producao.css       # Estilos da produção
│   └── logistica.css      # Estilos da logística
├── img/
│   └── logo.png           # Logo da empresa
├── Banco de Dados/        # Scripts SQL para criar tabelas
├── README.md              # Este arquivo
└── DOCUMENTACAO.md        # Documentação detalhada
```

---

## 💻 Uso do Sistema

### Primeiro Acesso

1. Acesse http://localhost/motor-genesis/
2. Faça login com suas credenciais
3. Você será redirecionado ao Dashboard

### Fluxo Principal

```
Login (index.php)
    ↓
Dashboard (dashboard.php)
    ├─ Estoque (estoque.php) - CRUD de peças
    ├─ Produção (producao.php) - Modelos e OPs
    └─ Logística (logistica.php) - Transportadoras e Envios
```

### Módulo Estoque
- Clique em "+" Nueva Peça
- Preencha os dados (Nome, Código, Quantidade, etc.)
- Salve e visualize na tabela

### Módulo Produção
- Cadastre modelos de motos
- Crie ordens de produção
- Acompanhe o status

### Módulo Logística
- Registre transportadoras
- Crie envios
- Rastreie status de entregas
- Monitore desempenho

---

## 🔧 Configuração do MySQL

### Com Senha (Recomendado para Produção)

```bash
# No terminal MySQL
CREATE USER 'motorgenesis'@'localhost' IDENTIFIED BY 'senha_segura_123';
GRANT ALL PRIVILEGES ON montadora.* TO 'motorgenesis'@'localhost';
FLUSH PRIVILEGES;
```

Após criar o usuário, atualize `conexao.php`:
```php
$user = "motorgenesis";
$pass = "senha_segura_123";
```

---

## 🐛 Troubleshooting

### Problema: "Erro de conexão ao banco de dados"
- Verifique se MySQL está rodando no XAMPP
- Confirme a senha em `conexao.php`
- Teste a conexão via phpMyAdmin

### Problema: "Página 404 - Não encontrada"
- Confirme que a pasta está em `C:\xampp\htdocs\motor-genesis\`
- Verifique se Apache está iniciado
- Limpe o cache do navegador

### Problema: "Sessão expirada"
- Faça novo login
- Verifique se cookies estão habilitados
- Confirme configuração de timeout em `seguranca.php`

### Problema: "Erro nas operações do banco"
- Verifique se todas as tabelas foram criadas
- Execute novamente os scripts da pasta `Banco de Dados/`
- Verifique permissões do usuário MySQL

---

## 📊 Backup e Manutenção

### Backup via phpMyAdmin
1. Acesse phpMyAdmin
2. Selecione banco "montadora"
3. Clique em "Exportar"
4. Escolha formato SQL
5. Clique em "Go"

### Backup via Terminal
```bash
mysqldump -u root -p montadora > backup_montadora.sql
```

### Restaurar Backup
```bash
mysql -u root -p montadora < backup_montadora.sql
```

---

## 📈 Funcionalidades Futuras

- [ ] Relatórios em PDF/Excel
- [ ] Gráficos de análise
- [ ] Gestão completa de usuários (CRUD)
- [ ] Notificações por email
- [ ] API REST
- [ ] Dashboard com KPIs avançados
- [ ] Aplicativo mobile complementar

---

## 📚 Documentação

Para informações detalhadas sobre:
- Instalação passo a passo
- Schema completo do banco
- Descrição de API interna
- Procedimentos avançados
- Segurança em produção

**Veja arquivo: `DOCUMENTACAO.md`**

---

## 👥 Níveis de Acesso

| Perfil | Permissões |
|--------|-----------|
| **ADMIN** | Acesso total ao sistema |
| **GERENTE** | Acesso a todos os módulos |
| **OPERADOR** | Acesso limitado a operações específicas |

---

## 🎯 Roadmap

**v1.1** (Próxima)
- Melhorias na segurança CSRF
- Auditoria de operações
- Download de relatórios

**v2.0** (Futuro)
- API REST completa
- Dashboard analytics
- Sistema de notificações

---

## 📝 Licença

Projeto acadêmico - Uso educacional e interno.

---

## 📞 Suporte

Para dúvidas ou sugestões:
1. Consulte a `DOCUMENTACAO.md`
2. Verifique os logs do servidor
3. Abra o console do navegador (F12)

---

**Motor Genesis** - Sistema de Gerenciamento Integrado para Montadora  
Desenvolvido com ❤️ para eficiência operacional

**Versão:** 1.0  
**Status:** ✅ Funcional e Testado  
**Última Atualização:** 2024

