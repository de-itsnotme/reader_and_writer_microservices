CREATE TABLE IF NOT EXISTS products (
    gtin VARCHAR(64) PRIMARY KEY,
    language VARCHAR(8) NOT NULL,
    title VARCHAR(255) NOT NULL,
    picture VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL
    );
