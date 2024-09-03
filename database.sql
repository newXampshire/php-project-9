CREATE TABLE IF NOT EXISTS urls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE,
    created_at DATETIME
);

CREATE TABLE IF NOT EXISTS url_checks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    url_id BIGINT NOT NULL REFERENCES urls (id),
    status_code VARCHAR(3),
    h1 VARCHAR(100),
    title VARCHAR(255),
    description TEXT,
    created_at DATETIME
);
