-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2015 at 07:56 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12
SET SESSION SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
--
-- Database: 'rotulabic'
--
CREATE DATABASE IF NOT EXISTS rotulabic DEFAULT CHARACTER SET utf8mb4 DEFAULT COLlATE utf8mb4_unicode_ci;
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
--
-- USER: 'rotulabic_user'
--
CREATE USER 'rotulabic_user'@'localhost' IDENTIFIED BY 'rotu_labic';
GRANT ALL ON rotulabic.* TO 'rotulabic_user'@'localhost';
USE rotulabic;
-- --------------------------------------------------------
--
-- Table structure for table 'aux_algorithm'
--
CREATE TABLE IF NOT EXISTS aux_algorithm (
  aux_algorithm varchar(50) NOT NULL,
  PRIMARY KEY (aux_algorithm)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Dumping data for table 'aux_algorithm'
--
INSERT INTO aux_algorithm (aux_algorithm) VALUES
('mostVoted'),
('random'),
('testMode'),
('transductive'),
('none'),
('PMIBased'),
('lexiconBased'),
('frequenceBased');
-- --------------------------------------------------------
--
-- Table structure for table 'aux_document_labeling_status'
--
CREATE TABLE IF NOT EXISTS aux_document_labeling_status (
  aux_status varchar(20) NOT NULL,
  PRIMARY KEY (aux_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Dumping data for table 'aux_document_labeling_status'
--
INSERT INTO aux_document_labeling_status (aux_status) VALUES
('finalized'),
('labeled'),
('skipped'),
('waiting');
-- --------------------------------------------------------
--
-- Table structure for table 'aux_labeling_process_status_values'
--
CREATE TABLE IF NOT EXISTS aux_labeling_process_status_values (
  aux_status varchar(20) NOT NULL,
  PRIMARY KEY (aux_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Table to store the possibles values of status field';
--
-- Dumping data for table 'aux_labeling_process_status_values'
--
INSERT INTO aux_labeling_process_status_values (aux_status) VALUES
('concluded'),
('draft'),
('in_analysis'),
('in_progress'),
('waiting');
--
-- Table structure for table 'aux_PMI_hits'
--
CREATE TABLE IF NOT EXISTS aux_PMI_hits (
  portuguese_negative int(11) NOT NULL DEFAULT 1,
  english_negative int(11) NOT NULL DEFAULT 1,
  portuguese_positive int(11) NOT NULL DEFAULT 1,
  english_positive int(11) NOT NULL DEFAULT 1
) ;
INSERT INTO aux_PMI_hits VALUES ();
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_chosen_label'
--
CREATE TABLE IF NOT EXISTS tbl_chosen_label (
  label_document int(11) NOT NULL,
  label_tagger int(11) NOT NULL,
  label_label varchar(50) NOT NULL,
  label_rank int(11) NOT NULL COMMENT 'If the label was a suggestion, then its rank is marked as -1',
  PRIMARY KEY (label_document,label_tagger,label_label),
  KEY fk_cLabel_tagger (label_tagger),
  KEY fk_cLabel_label (label_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_word_frequence'
--
CREATE TABLE IF NOT EXISTS tbl_word_frequence (
  wf_process int(11) NOT NULL,
  wf_frequence int(11) NOT NULL DEFAULT 0,
  wf_word varchar(50) NOT NULL,
  PRIMARY KEY (wf_process,wf_word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'aux_labeling_type'
--
CREATE TABLE IF NOT EXISTS aux_labeling_type (
  aux_labeling_type varchar(50) NOT NULL,
  PRIMARY KEY (aux_labeling_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Dumping data for table 'aux_labeling_type'
--
INSERT INTO aux_labeling_type (aux_labeling_type) VALUES
('normal'),
('annotation');
-- --------------------------------------------------------
--
-- Table structure for table 'aux_aspect_type'
--
CREATE TABLE IF NOT EXISTS aux_aspect_type(
  aux_aspect_type varchar(50) NOT NULL,
  PRIMARY KEY (aux_aspect_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
--
-- Dumping data for table 'aux_aspect_type'
--
INSERT INTO aux_aspect_type (aux_aspect_type) VALUES
('normal'),
('hidden'),
('generic');
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_document'
--
CREATE TABLE IF NOT EXISTS tbl_document (
  document_id int(11) NOT NULL AUTO_INCREMENT,
  document_process int(11) NOT NULL,
  document_text text CHARACTER SET utf8mb4 collate utf8mb4_unicode_ci NOT NULL,
  document_name varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  document_size int(11) NOT NULL,
  PRIMARY KEY (document_id),
  UNIQUE(document_name,document_process) COMMENT 'Cant have repeated document name on the same labelling process',
  KEY idx_doc_lp (document_process)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2953 ;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_document_labeling'
--
CREATE TABLE IF NOT EXISTS tbl_document_labeling (
  labeling_document int(11) NOT NULL,
  labeling_tagger int(11) NOT NULL,
  labeling_status varchar(20) NOT NULL DEFAULT 'waiting',
  labeling_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (labeling_document,labeling_tagger),
  KEY labeling_status (labeling_status),
  KEY fk_docLabeling_tagger (labeling_tagger)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_document_term'
--
CREATE TABLE IF NOT EXISTS tbl_document_term (
  term_id int(11) NOT NULL,
  term_document int(11) NOT NULL,
  term_count int(11) NOT NULL,
  PRIMARY KEY (term_id,term_document),
  KEY idx_term (term_id),
  KEY idx_document (term_document)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Count how many times a term appears on a document';
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_label'
--
CREATE TABLE IF NOT EXISTS tbl_label (
  label_label varchar(50) NOT NULL,
  PRIMARY KEY (label_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_labeling_process'
--
CREATE TABLE IF NOT EXISTS tbl_labeling_process (
  process_id int(11) NOT NULL AUTO_INCREMENT,
  process_name varchar(50) NOT NULL,
  process_admin int(11) NOT NULL,
  process_status varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'draft / in_progress / in_analysis / concluded ',
  process_label_acceptance_rate int(11) NOT NULL,
  process_multilabel tinyint(1) NOT NULL,
  process_type varchar(20) NOT NULL DEFAULT 'postSet',
  process_instructions text,
  process_suggestion_algorithm varchar(50) NOT NULL,
  process_labeling_type varchar(20) NOT NULL DEFAULT 'normal' COMMENT 'normal / annotation',
  process_aspect_suggestion_algorithm varchar(50) NOT NULL DEFAULT 'none',
  process_hidden_aspect tinyint(1) NOT NULL DEFAULT 1,
  process_generic_aspect tinyint(1) NOT NULL DEFAULT 1,
  process_translator tinyint(1) NOT NULL DEFAULT 0,
  process_language varchar(10) NOT NULL DEFAULT 'XX',
  PRIMARY KEY (process_id),
  UNIQUE KEY unique_name (process_name,process_admin),
  KEY idx_lp_status (process_status),
  KEY idx_lp_admin (process_admin),
  KEY process_suggestion_algorithm (process_suggestion_algorithm)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COMMENT='Process Type : True = Fixed / False = Free' AUTO_INCREMENT=29 ;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_pmi_phrases'
--
CREATE TABLE IF NOT EXISTS tbl_pmi_phrases (
  phrase varchar(100) NOT NULL,
  phrase_count bigint(16) NOT NULL,
  negative_count int(11) NOT NULL,
  positive_count int(11) NOT NULL,
  pmi_lp int(11) NOT NULL,
  PRIMARY KEY (phrase, pmi_lp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_labeling_process_label_option'
--
CREATE TABLE IF NOT EXISTS tbl_labeling_process_label_option (
  lpLabelOpt_lp int(11) NOT NULL,
  lpLabelOpt_label varchar(50) NOT NULL,
  lpLabelOpt_color varchar(30),
  PRIMARY KEY (lpLabelOpt_lp,lpLabelOpt_label),
  KEY fk_lpLabelOpt_label (lpLabelOpt_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_aspect'
--
CREATE TABLE IF NOT EXISTS tbl_aspect (
  aspect_tagger int(11) NOT NULL,
  aspect_doc int(11) NOT NULL,
  aspect_lp int(11) NOT NULL,
  aspect_type varchar(20) NOT NULL DEFAULT 'normal',
  aspect_aspect varchar(100),
  aspect_polarity varchar(50) NOT NULL,
  aspect_start int(5) NOT NULL,
  aspect_end int(5),
  aspect_number int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (aspect_number),
  UNIQUE(aspect_tagger, aspect_doc, aspect_lp, aspect_aspect, aspect_polarity, aspect_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_sentiment_indication'
--
CREATE TABLE IF NOT EXISTS tbl_sentiment_indication (
  si_term varchar(100) NOT NULL,
  si_real_text varchar(200) NOT NULL,
  si_start int(5) NOT NULL,
  si_end int(5),
  si_aspect_number int(11) NOT NULL,
  PRIMARY KEY (si_aspect_number, si_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_labeling_process_postset'
--
CREATE TABLE IF NOT EXISTS tbl_labeling_process_postset (
  postset_process int(11) NOT NULL,
  postset_suggestion_acceptance_rate int(11) NOT NULL,
  PRIMARY KEY (postset_process)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_labeling_process_tagger'
--
CREATE TABLE IF NOT EXISTS tbl_labeling_process_tagger (
  process_tagger_process int(11) NOT NULL,
  process_tagger_tagger int(11) NOT NULL,
  process_tagger_status varchar(20) NOT NULL DEFAULT 'waiting',
  PRIMARY KEY (process_tagger_process,process_tagger_tagger),
  KEY idx_lpTagger_tagger (process_tagger_tagger),
  KEY idx_lpTagger_status (process_tagger_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_labeling_process_transductive'
--
CREATE TABLE IF NOT EXISTS tbl_labeling_process_transductive (
  transductive_process int(11) NOT NULL,
  transductive_idiom varchar(5) NOT NULL,
  transductive_reset_rate int(11) NOT NULL,
  PRIMARY KEY (transductive_process)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_login_attempts'
--
CREATE TABLE IF NOT EXISTS tbl_login_attempts (
  la_user int(11) NOT NULL,
  la_time varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_ranked_label'
--
CREATE TABLE IF NOT EXISTS tbl_ranked_label (
  rLabel_document int(11) NOT NULL,
  rLabel_label varchar(50) NOT NULL,
  rLabel_accuracy float NOT NULL DEFAULT '0',
  PRIMARY KEY (rLabel_document,rLabel_label),
  KEY fk_rLabel_label (rLabel_label)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_suggestion'
--
CREATE TABLE IF NOT EXISTS tbl_suggestion (
  suggestion_document int(11) NOT NULL,
  suggestion_tagger int(11) NOT NULL,
  suggestion_algorithm varchar(50) NOT NULL,
  suggestion_label varchar(50) NOT NULL,
  PRIMARY KEY (suggestion_document,suggestion_tagger,suggestion_algorithm),
  KEY fk_suggestion_tagger (suggestion_tagger),
  KEY fk_suggestion_algorithm (suggestion_algorithm)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_term'
--
CREATE TABLE IF NOT EXISTS tbl_term (
  term_id int(11) NOT NULL AUTO_INCREMENT,
  term_term varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (term_id),
  UNIQUE KEY term_term (term_term)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=162043 ;
-- --------------------------------------------------------
--
-- Table structure for table 'tbl_user'
--
CREATE TABLE IF NOT EXISTS tbl_user (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  user_name varchar(50) NOT NULL,
  user_email varchar(50) NOT NULL,
  user_password char(128) NOT NULL,
  user_salt char(128) NOT NULL,
  user_role varchar(50) NOT NULL DEFAULT 'tagger',
  PRIMARY KEY (user_id),
  UNIQUE email(user_email)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=21 ;
--
-- Dumping data for table 'tbl_user'
--
INSERT INTO tbl_user (user_id, user_name, user_email, user_password, user_salt, user_role) VALUES
(16, 'admin', 'admin@admin.com', 'a9961b585e76dd66a9663dc532ac1127b77ac5b969b5825fa2fd525f1a5e048274e723cdbbe67a43362cd6639b16dc2f5e5b43feb4e549467a7844704b1e704f', '7b20f82f32f9e0ed599cf7c4e2dbb380d28a838386af6c396d95978a50316fcaaff335b085c281b29e10b8eb159565fb389dbe3a75605e0847cc290fd9726de7', 'processAdmin'),
(17, 'mario', 'mario@mario.com', '2f1446a149d4248e816f8c8db7c6d67a5dd08805250c51d59a9a49caecbb778470c427246124f83ac36bdef16da1a84e50685a62fe0c79765002e46a3a8e5dce', '10e378ecf766e16596d03677b62915139fa949d443c8d3ebd6f3350e8caa182cada9a57c268e5f54b374d659bac392fcde52e9e0c856505a55c8389f9508de21', 'tagger'),
(18, 'pedro', 'pedro@pedro.com', '4763d081a86fa8ef0385f3a751012989c0a301e11d99eddcf166c5922a51f2c2fad217fc0da9ff1d3701af73abf42b7536425aa9672b4a09a40992d392643065', 'a76c48c624f8d820df044cf7aba06ae42fd72b7b6b47810c3c974408f5b04579b3d646209ef59a6872b51452bc27ae094ae2d6f40266ab92dce5a7e9ea813a6f', 'tagger'),
(19, 'maria', 'maria@maria.com', '6828a294d06a98dee3b205b542572535851644ae4842fa321a4874c18e09272303df5df0ee594180e8d2e4b871adab955f91cc84579688732e71e0897fd4e53f', 'aaa1549d75346a6992e77be2d2d9d493eb3cceb656ad9a28a66dfe84709670fd606fdeb254a78b1af024e11afacf4144990f68ce0d314a8291896d63d08e8dd2', 'tagger'),
(20, 'rafael', 'rafael@rafael.com', '04f744526afa4d55a752401819715bb0b2ebec98a2b12a3bbf66400091bdf5a4b34b8fcec5be1694c607e2718ee7e5bf876be22e202ac8003052d9edecce7a86', '86675cb42abeb0cd4978d2f3c7155c6efaec7d11e09077af9c81f71c5e621177fc3313ed95d3470dd73da7fec264c3706ed40160cb0720acfe1f3216717592fe', 'tagger');
--
-- Constraints for dumped tables
--
--
-- Constraints for table 'tbl_chosen_label'
--
ALTER TABLE tbl_chosen_label
  ADD CONSTRAINT fk_cLabel_doc FOREIGN KEY (label_document) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_cLabel_label FOREIGN KEY (label_label) REFERENCES tbl_label (label_label) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_cLabel_tagger FOREIGN KEY (label_tagger) REFERENCES tbl_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_document'
--
ALTER TABLE tbl_document
  ADD CONSTRAINT fk_doc_lp FOREIGN KEY (document_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
--
-- Constraints for table 'tbl_document_labeling'
--
ALTER TABLE tbl_document_labeling
  ADD CONSTRAINT fk_docLabeling_allowed_status FOREIGN KEY (labeling_status) REFERENCES aux_document_labeling_status (aux_status) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_docLabeling_doc FOREIGN KEY (labeling_document) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_docLabeling_tagger FOREIGN KEY (labeling_tagger) REFERENCES tbl_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_document_term'
--
ALTER TABLE tbl_document_term
  ADD CONSTRAINT fk_docTerm_docId FOREIGN KEY (term_document) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_docTerm_termId FOREIGN KEY (term_id) REFERENCES tbl_term (term_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_labeling_process'
--
ALTER TABLE tbl_labeling_process
  ADD CONSTRAINT fk_lp_admin FOREIGN KEY (process_admin) REFERENCES tbl_user (user_id) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lp_algorithm FOREIGN KEY (process_suggestion_algorithm) REFERENCES aux_algorithm (aux_algorithm) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lp_aspect_suggestion_algorithm FOREIGN KEY (process_aspect_suggestion_algorithm) REFERENCES aux_algorithm (aux_algorithm) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lp_labeling_type FOREIGN KEY (process_labeling_type) REFERENCES aux_labeling_type (aux_labeling_type) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lp_allowed_status FOREIGN KEY (process_status) REFERENCES aux_labeling_process_status_values (aux_status) ON DELETE NO ACTION ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_pmi_phrases'
--
ALTER TABLE tbl_pmi_phrases
  ADD CONSTRAINT fk_pmi_lp FOREIGN KEY (pmi_lp) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
--
-- Constraints for table 'tbl_labeling_process_label_option'
--
ALTER TABLE tbl_labeling_process_label_option
  ADD CONSTRAINT fk_lpLabelOpt_label FOREIGN KEY (lpLabelOpt_label) REFERENCES tbl_label (label_label) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lpLabelOpt_lp FOREIGN KEY (lpLabelOpt_lp) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_aspect'
--
ALTER TABLE tbl_aspect
  ADD CONSTRAINT fk_aspect_type FOREIGN KEY (aspect_type) REFERENCES aux_aspect_type (aux_aspect_type) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_aspect_polarity FOREIGN KEY (aspect_polarity) REFERENCES tbl_labeling_process_label_option (lpLabelOpt_label) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_aspect_lp FOREIGN KEY (aspect_lp) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_aspect_tagger FOREIGN KEY (aspect_tagger) REFERENCES tbl_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_aspect_doc FOREIGN KEY (aspect_doc) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_sentiment_indication'
--
ALTER TABLE tbl_sentiment_indication
  ADD CONSTRAINT fk_si_aspect FOREIGN KEY (si_aspect_number) REFERENCES tbl_aspect (aspect_number) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_labeling_process_postset'
--
ALTER TABLE tbl_labeling_process_postset
  ADD CONSTRAINT fk_postset_process FOREIGN KEY (postset_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_labeling_process_tagger'
--
ALTER TABLE tbl_labeling_process_tagger
  ADD CONSTRAINT fk_lpTagger_lp FOREIGN KEY (process_tagger_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lpTagger_status FOREIGN KEY (process_tagger_status) REFERENCES aux_labeling_process_status_values (aux_status) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_lpTagger_tagger FOREIGN KEY (process_tagger_tagger) REFERENCES tbl_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_labeling_process_transductive'
--
ALTER TABLE tbl_labeling_process_transductive
  ADD CONSTRAINT fk_transductive_process FOREIGN KEY (transductive_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
  
  --
-- Constraints for table 'tbl_word_frequence'
--
ALTER TABLE tbl_word_frequence
  ADD CONSTRAINT fk_wf_process FOREIGN KEY (wf_process) REFERENCES tbl_labeling_process (process_id) ON DELETE CASCADE ON UPDATE CASCADE;
  
--
-- Constraints for table 'tbl_ranked_label'
--
ALTER TABLE tbl_ranked_label
  ADD CONSTRAINT fk_rLabel_doc FOREIGN KEY (rLabel_document) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_rLabel_label FOREIGN KEY (rLabel_label) REFERENCES tbl_label (label_label) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Constraints for table 'tbl_suggestion'
--
ALTER TABLE tbl_suggestion
  ADD CONSTRAINT fk_suggestion_algorithm FOREIGN KEY (suggestion_algorithm) REFERENCES aux_algorithm (aux_algorithm) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_suggestion_document FOREIGN KEY (suggestion_document) REFERENCES tbl_document (document_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_suggestion_tagger FOREIGN KEY (suggestion_tagger) REFERENCES tbl_user (user_id) ON DELETE CASCADE ON UPDATE CASCADE;
