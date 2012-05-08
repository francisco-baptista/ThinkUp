CREATE TABLE tu_insight_baselines (
  date date NOT NULL COMMENT 'Date of baseline statistic.',
  instance_id int(11) NOT NULL COMMENT 'Instance ID.',
  slug varchar(100) NOT NULL COMMENT 'Unique identifier for a type of statistic.',
  value int(11) NOT NULL COMMENT 'The numeric value of this stat/total/average.',
  UNIQUE KEY unique_base (date,instance_id,slug),
  KEY date (date,instance_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_general_ci COMMENT='Insight baseline statistics.';

CREATE TABLE tu_insights (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal unique ID.',
  instance_id int(11) NOT NULL COMMENT 'Instance ID.',
  slug varchar(100) NOT NULL COMMENT 'Identifier for a type of statistic.',
  `text` varchar(255) NOT NULL COMMENT 'Text content of the alert.',
  related_data text COMMENT 'Serialized related insight data, such as a list of users or a post.',
  `date` date NOT NULL COMMENT 'Date of insight.',
  emphasis int(11) NOT NULL DEFAULT '0' COMMENT 'Level of emphasis for insight presentation.',
  PRIMARY KEY (id),
  KEY instance_id (instance_id,slug,`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Insights for a given service user.';
