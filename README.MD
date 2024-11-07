Esse sistema foi projetado e desenvolvido por ZéMatheusDev

para rodar o sistema, basta seguir o passo a passo...

1- rodar composer install no terminal

2- rodar no terminal o comando mkdir -p config/jwt para criação da pasta jwt para configuração do token para o frontend

3- criar os 2 arquivos pem, rodar no terminal - openssl genpkey -algorithm RSA -out config/jwt/private.pem, sem criptografia na senha por ser um sistema desafio

4- criando o public.pem, rodar no terminal - openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

5- alterar no .env o JWT_PASSPHRASE para a senha que voce criou ao rodar o private.pem, por exemplo -

JWT_PASSPHRASE=1234

6- baixar e instalar o postgresql

7- criar 1 banco de dados com o nome que voce queira (informar no .env)

8- rodar o seguinte comando sql para criação das tabelas

-----------------------------------------------------------------------------------------------------------------------------------------

CREATE TABLE empresas (
    id SERIAL PRIMARY KEY, -- `SERIAL` 
    nome VARCHAR(100) DEFAULT NULL,
    cnpj VARCHAR(100) DEFAULT NULL UNIQUE, 
    token VARCHAR(255) DEFAULT NULL,
    status VARCHAR(255) DEFAULT NULL,
    deleted VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE public.usuarios (
    id SERIAL PRIMARY KEY, -- `SERIAL` define o campo como autoincrementado e chave primária
    nome VARCHAR(100) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL UNIQUE, -- Adicione `UNIQUE` se o email deve ser único
    senha VARCHAR(100) DEFAULT NULL,
    admin BOOLEAN DEFAULT false,
    token VARCHAR(255) DEFAULT NULL,
    status VARCHAR(255) DEFAULT NULL,
    deleted VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    id_empresa TEXT
);

INSERT INTO public.usuarios (nome, email, senha, admin, token, status, deleted, id_empresa)
VALUES ('Admin', 'admin@admin.com', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', true, 'e5d3f1b9a4c8f7e2b3a6d8c1f4a7b9e0', '1', '0', '0');

-----------------------------------------------------------------------------------------------------------------------------------------

9- agora com o banco de dados criado, verifique o .env para ver como esta o acesso ao banco de dados do pgadmin do seu servidor...

exemplo passando o username, password, ip e porta do banco, e o nome da database...

DATABASE_URL="postgresql://postgres:123@localhost:5433/bd_desafio?serverVersion=13"

10- agora com tudo configurado, voce já tem acesso ao sistema no frontend utilizando a conta admin do servidor que voce fez o insert

login - admin@admin.com
senha - 123

11- rode o symfony serve ou php -S localhost:8000 -t public para a porta 8000, caso esteja em uso trocar...

12- caso queira ver a documentação das APIs, acessar o localhost:8000/api/doc

13- va para o frontend e leia o readme, backend pronto pra uso!




