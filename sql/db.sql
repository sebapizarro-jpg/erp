-- =================================================================
-- 1. CREACIÓN DE LA BASE DE DATOS
-- =================================================================
CREATE DATABASE IF NOT EXISTS erp_industrial
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE erp_industrial;

-- =================================================================
-- 2. DICCIONARIOS Y TABLAS MAESTRAS (Sin dependencias previas)
-- =================================================================

-- Tabla de Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255)
);

-- Tabla de Estados del flujo productivo
CREATE TABLE estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255)
);

-- Tabla de Clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razon_social VARCHAR(150) NOT NULL,
    cuit_rut VARCHAR(50) NOT NULL UNIQUE,
    contacto VARCHAR(150),
    email VARCHAR(150),
    telefono VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Proveedores (Para piezas comerciales o talleres externos)
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    razon_social VARCHAR(150) NOT NULL,
    cuit_rut VARCHAR(50) NOT NULL UNIQUE,
    contacto VARCHAR(150),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Catálogo Maestro de Piezas
CREATE TABLE catalogo_piezas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sku VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    tipo_pieza ENUM('Standard', 'Variable', 'Comercial') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =================================================================
-- 3. TABLAS CON DEPENDENCIAS DE PRIMER NIVEL
-- =================================================================

-- Tabla de Usuarios (Depende de roles)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    legajo VARCHAR(20) UNIQUE NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Proyectos / Notas de Fabricación (Depende de clientes y estados)
CREATE TABLE proyectos_nf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_nf VARCHAR(50) NOT NULL UNIQUE,
    cliente_id INT NOT NULL,
    estado_id INT NOT NULL,
    fecha_recepcion DATE NOT NULL,
    fecha_entrega_estimada DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nf_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_nf_estado FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE RESTRICT
);

-- Stock e Inventario (Depende de catalogo_piezas)
CREATE TABLE stock_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pieza_catalogo_id INT NOT NULL UNIQUE,
    cantidad_fisica DECIMAL(10,2) DEFAULT 0.00,
    cantidad_reservada DECIMAL(10,2) DEFAULT 0.00,
    ubicacion VARCHAR(100),
    CONSTRAINT fk_stock_pieza FOREIGN KEY (pieza_catalogo_id) REFERENCES catalogo_piezas(id) ON DELETE CASCADE
);

-- Órdenes de Compra o Producción (Depende de proveedores y estados)
CREATE TABLE ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_orden ENUM('Interna', 'Proveedor', 'Taller_Externo') NOT NULL,
    proveedor_id INT NULL, -- NULL si es orden interna
    estado_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orden_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id) ON DELETE RESTRICT,
    CONSTRAINT fk_orden_estado FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE RESTRICT
);

-- =================================================================
-- 4. TABLAS TRANSACCIONALES PROFUNDAS Y RELACIONALES
-- =================================================================

-- Desglose de la Nota de Fabricación (Depende de NF, Piezas y Estados)
CREATE TABLE nf_desglose (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nf_id INT NOT NULL,
    pieza_catalogo_id INT NOT NULL,
    cantidad_requerida DECIMAL(10,2) NOT NULL,
    estado_id INT NOT NULL,
    CONSTRAINT fk_desglose_nf FOREIGN KEY (nf_id) REFERENCES proyectos_nf(id) ON DELETE CASCADE,
    CONSTRAINT fk_desglose_pieza FOREIGN KEY (pieza_catalogo_id) REFERENCES catalogo_piezas(id) ON DELETE RESTRICT,
    CONSTRAINT fk_desglose_estado FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE RESTRICT
);

-- Detalle de las Órdenes (Depende de Ordenes y Desglose de NF)
CREATE TABLE ordenes_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    nf_desglose_id INT NOT NULL, -- Vincula la compra/fabricación con la necesidad exacta de la NF
    cantidad DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_orden FOREIGN KEY (orden_id) REFERENCES ordenes(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_desglose FOREIGN KEY (nf_desglose_id) REFERENCES nf_desglose(id) ON DELETE RESTRICT
);

-- =================================================================
-- 5. TRAZABILIDAD Y AUDITORÍA
-- =================================================================

-- Trazabilidad de Estados (Tabla polimórfica, depende de usuarios y estados)
CREATE TABLE trazabilidad_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entidad_tipo ENUM('NF', 'Pieza_Desglose', 'Orden') NOT NULL,
    entidad_id INT NOT NULL, -- No tiene FK estricta porque el ID puede ser de NF, Desglose u Orden
    estado_anterior_id INT NULL,
    estado_nuevo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    motivo_observacion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_trazabilidad_est_ant FOREIGN KEY (estado_anterior_id) REFERENCES estados(id) ON DELETE RESTRICT,
    CONSTRAINT fk_trazabilidad_est_nue FOREIGN KEY (estado_nuevo_id) REFERENCES estados(id) ON DELETE RESTRICT,
    CONSTRAINT fk_trazabilidad_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
);

-- =================================================================
-- 6. DATOS INICIALES POR DEFECTO (Seeders)
-- =================================================================

-- Insertar Roles Básicos
INSERT INTO roles (nombre, descripcion) VALUES 
('Admin', 'Acceso total al sistema y configuraciones'),
('Supervisor', 'Gestiona NFs, aprueba órdenes y supervisa planta'),
('Calidad', 'Personal de QC, aprueba o rechaza piezas'),
('Operario', 'Personal de planta, registra avances de producción');

-- Insertar Estados del Sistema Productivo
INSERT INTO estados (nombre, descripcion) VALUES 
('Ingresado', 'Nota de fabricación recién creada'),
('Pendiente_Stock', 'A la espera de verificación o liberación de materiales'),
('En_Produccion', 'Actualmente en taller o maquinaria'),
('En_QC', 'Esperando revisión de Control de Calidad'),
('Aprobado', 'Revisión exitosa, listo para ensamble o entrega'),
('Rechazado', 'No pasó QC, requiere acciones correctivas'),
('En_Revision', 'Bajo análisis de ingeniería o cliente');