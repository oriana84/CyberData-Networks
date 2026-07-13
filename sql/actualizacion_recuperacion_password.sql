-- Ejecutar solo si la tabla usuario todavía no dispone de estos campos.
ALTER TABLE usuario
    ADD COLUMN token_recovery VARCHAR(64) NULL,
    ADD COLUMN token_expira DATETIME NULL;

CREATE INDEX idx_usuario_token_recovery ON usuario (token_recovery);
