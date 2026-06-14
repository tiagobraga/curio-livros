# Curió Livros

O **Curió Livros** é um sistema web de gestão de acervos bibliográficos com catálogo coletivo (multiunidades/multibibliotecas), projetado para ser moderno, leve, responsivo (Desktop & Mobile) e compatível com o padrão de metadados **BIBFRAME** (Linked Data).

Este projeto é desenvolvido com foco em facilidade de uso em pequenas e médias bibliotecas, permitindo a catalogação rápida de livros através do escaneamento do código de barras (ISBN) diretamente pela câmera do celular e autopreenchimento de metadados via APIs externas (Google Books e Open Library).

---

## 🚀 Tecnologias Utilizadas

*   **Backend:** PHP 8.3 + [Laravel 11](https://laravel.com/)
*   **Banco de Dados:** [PostgreSQL 15](https://www.postgresql.org/) (com armazenamento JSONB para metadados)
*   **Frontend:** HTML5, Tailwind CSS e Vanilla JS (integrando a biblioteca [html5-qrcode](https://github.com/mebjas/html5-qrcode))
*   **Arquitetura:** Dockerized (100% em containers)

---

## 📚 Modelo de Metadados: BIBFRAME

Diferente dos sistemas tradicionais que usam o formato legado MARC21, o Curió Livros adota o padrão **BIBFRAME** (Bibliographic Framework), o sucessor moderno baseado em Linked Data (Web Semântica). 

A modelagem de dados separa a informação em três níveis:
1.  **Work (Obra):** O conceito intelectual da obra (autor, título original, idioma original, assunto).
2.  **Instance (Instância):** A publicação física ou digital específica (editora, data de publicação, ISBN, número de páginas, edição).
3.  **Item (Exemplar):** A cópia física concreta em uma determinada biblioteca (número de chamada, localização na estante, código de barras/tombo local, status).

Os dados de **Work** e **Instance** são unificados no catálogo central e armazenados no formato estruturado JSON-LD dentro de um campo `JSONB` no PostgreSQL. Isso permite indexação de alta velocidade e total flexibilidade para estender ou adicionar novas chaves sem a necessidade de migrações de banco de dados no futuro.

---

## 🛠️ Estrutura de Diretórios

*   `/app` - Lógica da aplicação Laravel (Models, Controllers, Services).
*   `/database` - Migrações do banco de dados e seeders de teste.
*   `/resources` - Views (Blade) e assets de estilo (Tailwind CSS, JS).
*   `/docker` - Configurações específicas para execução via Docker (Nginx, PHP-FPM).

---

## 📦 Como Executar (Ambiente de Desenvolvimento)

### Pré-requisitos
*   [Docker](https://www.docker.com/) e [Docker Compose](https://docs.docker.com/compose/) instalados no host.

### Passos para Inicialização
1.  Clone o repositório:
    ```bash
    git clone https://github.com/tiagobraga/curio-livros.git
    cd curio-livros
    ```
2.  Construa e inicie os containers Docker:
    ```bash
    docker compose up -d --build
    ```
3.  Instale as dependências do Composer dentro do container:
    ```bash
    docker compose exec app composer install
    ```
4.  Copie o arquivo de configuração de ambiente e gere a chave de criptografia:
    ```bash
    docker compose exec app cp .env.example .env
    ```
5.  Execute as migrations e seeders de teste do banco de dados:
    ```bash
    docker compose exec app php artisan migrate --seed
    ```
6.  Acesse o painel web em `http://localhost:8088`.

---

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - consulte o arquivo [LICENSE](LICENSE) para obter detalhes.
