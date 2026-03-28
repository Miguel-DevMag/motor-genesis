# 🎯 COMO TESTAR O MOTOR GENESIS

## Acesso Rápido

### 1. **Login no Sistema**
- URL: `http://localhost/motor-genesis/index.php`
- Credenciais padrão: Use qualquer usuário cadastrado no banco
- Alternativa: Crie um novo usuário em `http://localhost/motor-genesis/cadastro.php`

### 2. **Verificar Instalação**
- Teste de conexão: `http://localhost/motor-genesis/teste.php`
- Checklist visual: `http://localhost/motor-genesis/verificacao.html`
- Status completo: `http://localhost/motor-genesis/PRONTO_PARA_PRODUCAO.html`

---

## ✅ Checklist de Testes

### Dashboard
- [ ] Acessar http://localhost/motor-genesis/dashboard.php
- [ ] Verificar cores preto e vermelho
- [ ] Clicar em cada menu (sidebar esquerda)

### Estoque (PRIORIDADE 1)
- [ ] Ir para: Estoque
- [ ] **Teste 1 - ADD**: Clicar em "Adicionar Peça", preencher campo, salvar
- [ ] **Teste 2 - EDIT**: Clicar no ícone de editar (lápis), mudar dados, atualizar
- [ ] **Teste 3 - DELETE**: Clicar no ícone de deletar (lixo), confirmar
- [ ] **Teste 4 - SEARCH**: Digitar nome/código na busca, verificar filtro

### Produção
- [ ] Ir para: Produção
- [ ] **Teste 1 - Novo Modelo**: Clicar "Novo Modelo", preencher, salvar
- [ ] **Teste 2 - Editar Modelo**: Clicar lápis no modelo, editar, salvar
- [ ] **Teste 3 - Deletar Modelo**: Clicar lixo no modelo, confirmar
- [ ] **Teste 4 - Nova OP**: Clicar "Nova OP", selecionar modelo, preencher, salvar
- [ ] **Teste 5 - Editar OP**: Clicar lápis na OP, editar, salvar
- [ ] **Teste 6 - Deletar OP**: Clicar lixo na OP, confirmar

### Orçamentos (NOVO - PRIORIDADE 2)
- [ ] Ir para: Orçamentos
- [ ] **Teste 1 - ADD**: Adicionar orçamento, salvar
- [ ] **Teste 2 - EDIT**: Editar orçamento, salvar
- [ ] **Teste 3 - DELETE**: Deletar orçamento, confirmar
- [ ] **Teste 4 - SEARCH**: Buscar por número/cliente

### Relatórios (NOVO)
- [ ] Ir para: Relatórios
- [ ] Ver estatísticas em tempo real
- [ ] Verificar cards com números (estoque, produção, etc)
- [ ] Observar tabelas com dados

### Usuários (NOVO - PRIORIDADE 3)
- [ ] Ir para: Usuários
- [ ] **Teste 1 - ADD**: Adicionar novo usuário, salvar
- [ ] **Teste 2 - EDIT**: Editar usuário, salvar
- [ ] **Teste 3 - DELETE**: Deletar usuário (exceto você mesmo)
- [ ] **Teste 4 - SEARCH**: Buscar por nome/email/login

### Logística
- [ ] Ir para: Logística
- [ ] Verificar se carrega sem erros
- [ ] Confirmar SQL foi corrigido

---

## 📊 Dados de Teste

### Adicionar Peça de Teste
```
Nome: Pneu Dianteiro
Código: PNEU001
Quantidade: 50
Custo Unitário: R$ 150,00
Preço Venda: R$ 300,00
Categoria: Pneus
```

### Adicionar Orçamento de Teste
```
Número: ORC001
Cliente: Teste Cliente
Valor: R$ 5.000,00
Status: Pendente
```

### Adicionar Usuário de Teste
```
Nome: Usuário Teste
Email: teste@example.com
Login: teste
Matrícula: 001
Perfil: Usuário
Senha: 123456
```

---

## 🎨 Verificar Design

- [ ] **Cores Preto (#1a1a1a)** - Fundo principal
- [ ] **Cor Vermelho (#dc143c)** - Títulos, botões, bordas
- [ ] **Menu Lateral** - Preto com borda vermelha esquerda
- [ ] **Botões** - Vermelho ao passar mouse
- [ ] **Responsividade** - Redimensionar janela, testar mobile

---

## 🔍 Testes de Validação

### Campos Obrigatórios
- [ ] Tentar salvar formulário vazio → Deve retornar erro
- [ ] Preencher apenas um campo → Deve retornar erro
- [ ] Preencher corretamente → Deve salvar

### Confirmação de Exclusão
- [ ] Ao clicar delete, deve aparecer popup "Tem certeza?"
- [ ] Se clicar "OK" → Deleta
- [ ] Se clicar "Cancelar" → Não deleta

### Search
- [ ] Digitar nome/código/email existente → Deve filtrar
- [ ] Digitar texto que não existe → Deve mostrar "Sem resultados"
- [ ] Voltar com botão "Limpar" → Deve mostrar todos os dados

---

## 🌐 Testar Responsividade

1. **Desktop** (1920x1080): Abrir navegador normalmente
2. **Tablet** (768x1024): 
   - Pressione F12 (DevTools)
   - Clique no ícone do celular/tablet
   - Selecione "iPad"
3. **Mobile** (375x667):
   - DevTools → Selecione "iPhone 12"
   - Verificar if menu está acessível

---

## 🐛 Se Encontrar Erros

1. **Erro ao acessar página**
   - Verificar arquivo PHP existe em `/motor-genesis/`
   - Verificar permissões do arquivo
   - Checar console do navegador (F12)

2. **Erro ao salvar dados**
   - Verificar conexão MySQL (teste.php)
   - Checar if tabelas existem no banco
   - Ver mensagem de erro exata

3. **Cores ou CSS não aparecendo**
   - Pressionar Ctrl+F5 (forçar recarregar)
   - Verificar if `css/tema.css` existe
   - Checar if o arquivo foi linkado corretamente

---

## 📝 Documentação Adicional

- `PROJETO_CONCLUIDO.md` - Resumo do que foi feito
- `PRONTO_PARA_PRODUCAO.html` - Checklist visual
- `README.md` - Documentação geral
- `DOCUMENTACAO.md` - Documentação técnica

---

## 🚀 Próximos Passos (Após Testes)

1. Criar backup do banco de dados
2. Duplicar ambiente para staging/teste
3. Treinar usuários
4. Importar dados reais (se aplicável)
5. Ir para produção

---

## 📞 Suporte

Se tiver dúvidas:
- Verifique os arquivos de documentação
- Cheque o arquivo `teste.php` para diagnosticar problemas
- Acesse `PRONTO_PARA_PRODUCAO.html` para checklist completo

---

**✅ Sistema pronto! Comece os testes agora!**

Data: 2024
Versão: 1.0
Status: PRONTO PARA TESTES E PRODUÇÃO
