-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: oficinahotrods_joaodavi_banco_projeto
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `oficinahotrods_joaodavi_banco_projeto`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `oficinahotrods_joaodavi_banco_projeto` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `oficinahotrods_joaodavi_banco_projeto`;

--
-- Table structure for table `demandas`
--

DROP TABLE IF EXISTS `demandas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `demandas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `solicitante_id` int(11) NOT NULL,
  `voluntario_id` int(11) DEFAULT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text NOT NULL,
  `tipo_peca` varchar(100) DEFAULT NULL,
  `status` enum('aberta','em_andamento','concluida','cancelada') NOT NULL DEFAULT 'aberta',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_demandas_solicitante` (`solicitante_id`),
  KEY `fk_demandas_voluntario` (`voluntario_id`),
  KEY `idx_demandas_status` (`status`),
  CONSTRAINT `fk_demandas_solicitante` FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_demandas_voluntario` FOREIGN KEY (`voluntario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `demandas`
--

LOCK TABLES `demandas` WRITE;
/*!40000 ALTER TABLE `demandas` DISABLE KEYS */;
INSERT INTO `demandas` VALUES (1,1,NULL,'Adaptador de escova de dente','Preciso de um adaptador ergonômico para segurar a escova de dente, dificuldade motora na mão direita.','ferramenta ergonômica','aberta','2026-10-22 12:34:30','2026-10-22 12:34:30'),(2,2,3,'Órtese simples para dedo','Órtese leve para imobilização parcial do dedo indicador, uso temporário.','órtese','em_andamento','2026-10-22 12:34:30','2026-10-22 12:34:30'),(3,1,4,'Suporte adaptado para talher','Suporte para facilitar preensão de talher durante refeições.','ferramenta ergonômica','concluida','2026-10-22 12:34:30','2026-10-22 12:34:30');
/*!40000 ALTER TABLE `demandas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_status`
--

DROP TABLE IF EXISTS `historico_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `historico_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `demanda_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `status_anterior` enum('aberta','em_andamento','concluida','cancelada') DEFAULT NULL,
  `status_novo` enum('aberta','em_andamento','concluida','cancelada') NOT NULL,
  `observacao` text DEFAULT NULL,
  `data_alteracao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_historico_usuario` (`usuario_id`),
  KEY `idx_historico_demanda` (`demanda_id`),
  CONSTRAINT `fk_historico_demanda` FOREIGN KEY (`demanda_id`) REFERENCES `demandas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_status`
--

LOCK TABLES `historico_status` WRITE;
/*!40000 ALTER TABLE `historico_status` DISABLE KEYS */;
INSERT INTO `historico_status` VALUES (1,2,3,'aberta','em_andamento','Voluntário aceitou a demanda e iniciou a impressão.','2026-10-22 12:34:30'),(2,3,4,'aberta','em_andamento','Peça em processo de impressão.','2026-10-22 12:34:30'),(3,3,4,'em_andamento','concluida','Peça impressa e entregue ao solicitante.','2026-10-22 12:34:30');
/*!40000 ALTER TABLE `historico_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('solicitante','clinica','voluntario','admin') NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Maria Solicitante','maria@exemplo.com','$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe','solicitante','(35) 99999-0001','São Lourenço','2026-10-22 12:34:30'),(2,'Clínica Vida Nova','clinica@exemplo.com','$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe','clinica','(35) 99999-0002','São Lourenço','2026-10-22 12:34:30'),(3,'João Voluntário','joao@exemplo.com','$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe','voluntario','(35) 99999-0003','Varginha','2026-10-22 12:34:30'),(4,'Ana Voluntária','ana@exemplo.com','$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe','voluntario','(35) 99999-0004','Pouso Alegre','2026-10-22 12:34:30'),(5,'Administrador','admin@conecta3d.com','$2y$10$bpYnaEENIk3q2L6yJUJ4pub6.gRD4MDSg3s7.1NIxncdokCgKIeSe','admin',NULL,NULL,'2026-10-22 12:55:41');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 15:04:41
