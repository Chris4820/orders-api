# Mini-API de Encomendas

Mini-API de encomendas desenvolvida em Laravel como resposta a um exercício técnico.

O projecto disponibiliza uma API REST para gestão de produtos e encomendas, um painel administrativo com Filament e um frontend em Vue.js para consulta de produtos e criação de encomendas.

O foco do projecto foi manter uma implementação simples, estruturada e funcional, dando especial atenção à validação, gestão de stock, consistência dos dados e testes automatizados.

---

## Funcionalidades

### Produtos

- Criar produtos
- Listar produtos
- Editar produtos
- Remover produtos através de Soft Delete
- Gerir preço e stock
- Seed inicial com 5 produtos

### Encomendas

- Criar encomendas através da API
- Associar vários produtos a uma encomenda
- Validar produtos existentes
- Validar stock disponível
- Actualizar automaticamente o stock após a criação da encomenda
- Guardar o preço do produto no momento da encomenda
- Listar encomendas com os respectivos produtos
- Consultar uma encomenda individual
- Cancelar encomendas
- Repor o stock dos produtos após o cancelamento
- Estados de encomenda:
    - `pending`
    - `paid`
    - `cancelled`

### Administração

Painel administrativo desenvolvido com Filament para:

- Gerir produtos
- Criar e editar produtos
- Consultar stock e preços
- Listar encomendas
- Consultar os detalhes das encomendas
- Criar e editar encomendas

### Frontend

Frontend desenvolvido em Vue.js que permite:

- Listar produtos
- Consultar preços e stock
- Adicionar produtos ao carrinho
- Alterar quantidades
- Remover produtos do carrinho
- Preencher os dados do cliente
- Criar uma encomenda
- Apresentar notificações de sucesso e erro

O checkout utiliza VeeValidate e Zod para validação dos dados.

### Testes

Foram adicionados testes automatizados com PHPUnit para validar os principais fluxos da API, incluindo:

- Gestão de produtos
- Validação de dados
- Criação de encomendas
- Validação de stock
- Produtos inexistentes
- Produtos duplicados
- Cancelamento de encomendas
- Reposição de stock

### Documentação da API

A API está documentada através de Swagger/OpenAPI.

A documentação pode ser consultada através da rota disponibilizada pelo projecto:

```text
/api/documentation
```

---

## Stack

### Backend

- PHP 8.4
- Laravel 13
- Eloquent ORM
- REST API
- PHPUnit

### Administração

- Filament 5

### Frontend

- Vue 3
- Vite
- Tailwind CSS
- Axios
- TanStack Vue Query
- VeeValidate
- Zod
- Vue Sonner

### Documentação

- OpenAPI / Swagger
- L5-Swagger

---

## Requisitos

Para executar o projecto localmente são necessários:

- PHP 8.4 ou compatível com Laravel 13
- Composer
- Node.js
- npm
- Uma base de dados suportada pelo Laravel

---

## Instalação

Clonar o repositório:

```bash
git clone https://github.com/Chris4820/orders-api.git
```

Entrar na pasta do projecto:

```bash
cd orders-api
```

### Setup inicial do projeto

Para instalar as dependências, configurar o ambiente e preparar a base de dados, executar na raiz do projeto:

```bash
composer run setup
```

Este comando instala as dependências do PHP e JavaScript, cria o ficheiro `.env` caso não exista, gera a chave da aplicação e executa as migrations e os seeders da base de dados.

## Executar o projecto

Iniciar o servidor Laravel:

```bash
php artisan serve
```

Noutro terminal, iniciar o Vite:

```bash
npm run dev
```

A aplicação ficará disponível, por defeito, em:

```text
http://localhost:8000
```

### Frontend

```text
http://localhost:8000/
```

### API

```text
http://localhost:8000/api
```

### Painel administrativo

```text
http://localhost:8000/admin
```

Para criar um utilizador administrador do Filament:

```bash
php artisan make:filament-user
```

### Swagger

```text
http://localhost:8000/api/documentation
```

## Exemplo de criação de encomenda

`POST /api/orders`

```json
{
    "customer_name": "João Silva",
    "customer_email": "joao@example.com",
    "products": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ]
}
```

## Gestão de stock

A criação de uma encomenda é executada dentro de uma transacção de base de dados.

Os produtos envolvidos são bloqueados para actualização através de `lockForUpdate()`, evitando que operações concorrentes consumam o mesmo stock de forma inconsistente.

Quando a encomenda é criada:

1. Os produtos são validados.
2. O stock disponível é verificado.
3. A encomenda é criada.
4. Os respectivos `order_items` são criados.
5. O stock dos produtos é decrementado.

Se alguma operação falhar, a transacção é revertida.

### Cancelamento

Quando uma encomenda é cancelada:

1. Os produtos da encomenda são carregados.
2. O stock correspondente às quantidades da encomenda é reposto.
3. O estado da encomenda passa para `cancelled`.

Uma encomenda já cancelada não pode ser cancelada novamente.

---

## Decisões técnicas

### Filament

Foi escolhido o Filament para o dashboard administrativo por permitir construir rapidamente uma interface de administração integrada com os modelos Eloquent.

Desta forma, foi possível concentrar o desenvolvimento na lógica da aplicação e da API, mantendo o backoffice simples e funcional.

### Vue.js

O frontend foi desenvolvido em Vue.js e integrado directamente no projecto Laravel através do Vite.

Esta abordagem permite manter o frontend e o backend no mesmo projecto, enquanto o frontend comunica com a API REST.

### TanStack Vue Query

Foi utilizado o TanStack Vue Query para gerir as operações assíncronas relacionadas com a API, nomeadamente a consulta dos produtos e a criação de encomendas.

Para além de simplificar a gestão dos estados de carregamento e erro, o Vue Query disponibiliza um sistema de cache integrado, permitindo reutilizar dados previamente obtidos e reduzir pedidos HTTP desnecessários à API.

### Validação

A validação é realizada tanto no frontend como no backend.

No frontend, VeeValidate e Zod são utilizados para validar os dados introduzidos no checkout.

No backend, o Laravel valida novamente todos os dados recebidos.

O backend é sempre a fonte de verdade para regras como stock, preços e existência dos produtos.

### Preço histórico

O preço do produto é guardado em `order_items.unit_price` no momento da criação da encomenda.

Isto permite manter o preço histórico da encomenda mesmo que o preço do produto seja alterado posteriormente.

### Soft Delete

Os produtos utilizam Soft Delete para evitar a remoção definitiva de produtos que possam estar associados a encomendas existentes.

Desta forma, é possível preservar o histórico das encomendas.

### Transacções e concorrência

A utilização de transacções e `lockForUpdate()` na criação e cancelamento das encomendas foi uma decisão deliberada para manter a consistência do stock perante operações concorrentes.

---

## Testes

Os testes podem ser executados com:

```bash
php artisan test
```

Os testes utilizam `RefreshDatabase`, garantindo que cada execução utiliza uma base de dados de teste isolada.

## Possíveis melhorias

Algumas funcionalidades que poderiam ser adicionadas numa evolução do projecto:

- Gestão de clientes como entidade independente
- Paginação e filtros nos endpoints de listagem
- Mais testes de integração e cenários de concorrência
