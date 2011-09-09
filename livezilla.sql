-- MySQL dump 10.13  Distrib 5.5.12, for FreeBSD8.2 (i386)
--
-- Host: localhost    Database: livezilla
-- ------------------------------------------------------
-- Server version	5.5.12-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alerts` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `receiver_user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `event_action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `text` mediumtext COLLATE utf8_bin NOT NULL,
  `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver_user_id` (`receiver_user_id`),
  CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`receiver_user_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alerts`
--

LOCK TABLES `alerts` WRITE;
/*!40000 ALTER TABLE `alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_archive`
--

DROP TABLE IF EXISTS `chat_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_archive` (
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `endtime` int(11) unsigned NOT NULL DEFAULT '0',
  `closed` int(11) unsigned NOT NULL DEFAULT '0',
  `chat_id` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `external_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `fullname` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `internal_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `area_code` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `html` longtext COLLATE utf8_bin NOT NULL,
  `plain` longtext COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `company` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `iso_language` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '',
  `iso_country` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',
  `host` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',
  `gzip` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `transcript_sent` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `transcript_receiver` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `question` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `customs` text COLLATE utf8_bin NOT NULL,
  KEY `chat_id` (`chat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_archive`
--

LOCK TABLES `chat_archive` WRITE;
/*!40000 ALTER TABLE `chat_archive` DISABLE KEYS */;
INSERT INTO `chat_archive` VALUES (1310885497,1310885668,1310885669,'11703','7e1194a677','訪客','9baf6c1','Support','SERVERPAGE','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>System</TD>\r\n<TD class=FCM3>&nbsp;14:51:19</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT face=Verdana color=#000000 size=2>Chat Request for <B>Support</B> (Administrator):<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=14></TD>\r\n<TD noWrap>Name:&nbsp;&nbsp;</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:&nbsp;&nbsp;</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>Company:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>Question:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT face=Verdana color=#000000 size=2>Chat has been accepted by <B>Administrator</B>.</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;14:51:37</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT face=Verdana color=#000000 size=2><FONT face=Verdana color=#000000 size=2>Hello 訪客, my name is Administrator, how may I help you?</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT size=2><SPAN class=SMILEYSMILE></SPAN></FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;14:52:30</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><SPAN class=SMILEYSLEEP></SPAN></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB>1。</TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCA>自动输入用户名</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;14:53:18</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5>2. how\'s delay.</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;14:53:28</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5>yeah，我同意。</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB>还有界面的修改</TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCA>主要看延迟咯。</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;14:54:03</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><SPAN class=SMILEYNEUTRAL></SPAN></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>System</TD>\r\n<TD class=FCM3>&nbsp;14:54:29</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT face=Verdana color=#000000 size=2><B>訪客</B> has left the Chat with <B>Administrator</B>.</FONT></TD></TR></TBODY></TABLE></DIV>','LiveZilla Chat Transcript\r\n\r\nDate: Sun, 17 Jul 2011 14:54:29 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11703\r\n-------------------------------------------------------------\r\n| 17.07.2011 14:51:39 | Administrator: Hello 訪客, my name is Administrator, how may I help you?\r\n| 17.07.2011 14:52:13 | Administrator: :-)\r\n| 17.07.2011 14:52:30 | 訪客: zzZZ\r\n| 17.07.2011 14:52:59 | 訪客: 1。\r\n| 17.07.2011 14:53:07 | 訪客: 自动输入用户名\r\n| 17.07.2011 14:53:18 | Administrator: 2. how\'s delay.\r\n| 17.07.2011 14:53:28 | 訪客: yeah，我同意。\r\n| 17.07.2011 14:53:35 | 訪客: 还有界面的修改\r\n| 17.07.2011 14:53:43 | 訪客: 主要看延迟咯。\r\n| 17.07.2011 14:54:12 | Administrator: :-|','','','ZH','CN','Unknown','203.82.42.134',0,1,'','','a:0:{}'),(1310970734,1310971143,1310971145,'11711','536041ba21','訪客','9baf6c1','Support','SERVERPAGE','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;14:32:12</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000000 size=2 face=Verdana>洽谈请求 <B>Support</B>:<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=15></TD>\r\n<TD width=90>名字:</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>接受者: </TD>\r\n<TD>Administrator</TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题: </TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#000000 size=2 face=Verdana><B>Administrator</B>已经接受洽谈.</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;14:32:15</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000000 size=2 face=Verdana><FONT color=#000000 size=2 face=Verdana>Hello 訪客, my name is Administrator, how may I help you?</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT size=2><SPAN class=SMILEYNEUTRAL></SPAN></FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;14:32:36</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5>你好。我想试试上传文件。</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;14:32:46</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5>ok, go ahead.</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;14:33:11</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5></TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=4 cellPadding=4 width=\"100%\">\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCF cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCQF1></TD>\r\n<TD class=FCQF2></TD>\r\n<TD class=FCQF3></TD></TR>\r\n<TR>\r\n<TD class=FCQF4></TD>\r\n<TD class=FCQF5>\r\n<TABLE class=TCQM cellSpacing=3>\r\n<TBODY>\r\n<TR>\r\n<TD align=center>\r\n<TABLE cellSpacing=0 cellPadding=0 align=right>\r\n<TBODY>\r\n<TR>\r\n<TD><STRONG>&nbsp;nginx_php_mysql_ha.png</STRONG></TD>\r\n<TD width=100><A class=FILEREQUESTLINK_ALLOW name=file_request_allow target=_self>接受</A></TD>\r\n<TD width=75><A class=FILEREQUESTLINK_REJECT name=file_request_reject target=_self>拒绝</A></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD>\r\n<TD class=FCQF6></TD></TR>\r\n<TR>\r\n<TD class=FCQF7></TD>\r\n<TD class=FCQF8></TD>\r\n<TD class=FCQF9></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;14:33:14</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000000 size=2 face=Verdana>You permitted <B>訪客</B> to upload <B>nginx_php_mysql_ha.png</B> to your server. As soon as the file has been uploaded to the server you will get the possibility to download the file.</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#000000 size=2 face=Verdana><B>訪客</B> 已离开洽谈<B>Administrator</B>.</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>','LiveZilla Chat Transcript\r\n\r\nDate: Mon, 18 Jul 2011 14:39:05 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11711\r\n-------------------------------------------------------------\r\n| 18.07.2011 14:32:15 | Administrator: Hello 訪客, my name is Administrator, how may I help you?\r\n| 18.07.2011 14:32:21 | Administrator: :-|\r\n| 18.07.2011 14:32:36 | 訪客: 你好。我想试试上传文件。\r\n| 18.07.2011 14:32:45 | Administrator: ok, go ahead.\r\n| 18.07.2011 14:33:09 | 訪客: FILE UPLOAD REQUEST (nginx_php_mysql_ha.png / ACCEPTED)','','','ZH','CN','ip-converge.27.208.203.in-addr.arpa','203.208.27.251',0,1,'','','a:0:{}'),(1310971575,1310971617,1310971617,'11713','536041ba21','訪客','9baf6c1','Support','SERVERPAGE','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;14:46:13</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000000 size=2 face=Verdana>洽谈请求 <B>Support</B>:<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=15></TD>\r\n<TD width=90>名字:</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>接受者: </TD>\r\n<TD>Administrator</TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题: </TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#000000 size=2 face=Verdana><B>Administrator</B>已经接受洽谈.</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;14:46:16</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000000 size=2 face=Verdana><FONT color=#000000 size=2 face=Verdana>Hello 訪客, my name is Administrator, how may I help you?</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;14:46:44</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5></TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=4 cellPadding=4 width=\"100%\">\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCF cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCQF1></TD>\r\n<TD class=FCQF2></TD>\r\n<TD class=FCQF3></TD></TR>\r\n<TR>\r\n<TD class=FCQF4></TD>\r\n<TD class=FCQF5>\r\n<TABLE class=TCQM cellSpacing=3>\r\n<TBODY>\r\n<TR>\r\n<TD align=center>\r\n<TABLE cellSpacing=0 cellPadding=0 align=right>\r\n<TBODY>\r\n<TR>\r\n<TD><STRONG>&nbsp;ball_blue.png</STRONG></TD>\r\n<TD width=100><A class=FILEREQUESTLINK_ALLOW name=file_request_allow target=_self>接受</A></TD>\r\n<TD width=75><A class=FILEREQUESTLINK_REJECT name=file_request_reject target=_self>拒绝</A></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD>\r\n<TD class=FCQF6></TD></TR>\r\n<TR>\r\n<TD class=FCQF7></TD>\r\n<TD class=FCQF8></TD>\r\n<TD class=FCQF9></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;14:46:45</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000000 size=2 face=Verdana>You permitted <B>訪客</B> to upload <B>ball_blue.png</B> to your server. As soon as the file has been uploaded to the server you will get the possibility to download the file.</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#000000 size=2 face=Verdana><B>ball_blue.png</B> 文件上传失败，或已中止。 文件尺寸超过服务器限制，被服务器拒绝。</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCA><FONT color=#000000 size=2 face=Verdana><B>訪客</B> 已离开洽谈<B>Administrator</B>.</FONT></TD></TR></TBODY></TABLE></DIV>','LiveZilla Chat Transcript\r\n\r\nDate: Mon, 18 Jul 2011 14:46:57 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11713\r\n-------------------------------------------------------------\r\n| 18.07.2011 14:46:16 | Administrator: Hello 訪客, my name is Administrator, how may I help you?\r\n| 18.07.2011 14:46:39 | 訪客: FILE UPLOAD REQUEST (ball_blue.png / ACCEPTED)','','','ZH','CN','ip-converge.27.208.203.in-addr.arpa','203.208.27.251',0,1,'','','a:0:{}'),(1312255120,1312255120,1312255120,'11733','536041ba21','訪客','9baf6c1','Support','','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;17:56:20</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000080 size=2 face=微软雅黑>对话请求 <B>客服部</B> (Administrator):<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=14></TD>\r\n<TD noWrap>名字:&nbsp;&nbsp;</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:&nbsp;&nbsp;</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#000080 size=2 face=微软雅黑>对话已由 <B>Administrator</B> 接受。</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;17:56:41</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000080 size=2 face=微软雅黑><FONT color=#000080 size=2 face=微软雅黑></FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#000080 face=微软雅黑>你好。请问有什么可以帮您？</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;17:59:06</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><SPAN class=SMILEYCOOL></SPAN></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><SPAN class=SMILEYLOL></SPAN><SPAN class=SMILEYLOL></SPAN></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;17:59:31</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#000080 face=微软雅黑></FONT><FONT color=#004080 face=微软雅黑><SPAN class=SMILEYWINK></SPAN></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;18:00:06</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>Administrator (root) 已离开对话。(用户已离线)</FONT></TD></TR></TBODY></TABLE></DIV>','在线客服 Chat Transcript\r\n\r\nDate: Tue, 02 Aug 2011 11:18:40 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11733\r\n-------------------------------------------------------------\r\n| 01.08.2011 17:56:50 | Administrator: \r\n| 01.08.2011 17:57:21 | Administrator: 你好。请问有什么可以帮您？\r\n| 01.08.2011 17:59:14 | 訪客: 8-)\r\n| 01.08.2011 17:59:26 | 訪客: :-]:-]\r\n| 01.08.2011 17:59:40 | Administrator: ;-)','','','ZH','CN','119.92.66.66.static.pldt.net','119.92.66.xxx',0,1,'','','a:0:{}'),(1312255160,1312255539,1312255539,'11734','536041ba21','訪客','9baf6c1','Support','SERVERPAGE','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;11:19:02</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>对话请求 <B>客服部</B> (Administrator):<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=14></TD>\r\n<TD noWrap>名字:&nbsp;&nbsp;</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:&nbsp;&nbsp;</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑>对话已由 <B>Administrator</B> 接受。</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;11:19:11</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑><FONT color=#004080 size=2 face=微软雅黑>欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;11:19:29</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><SPAN class=SMILEYSLEEP></SPAN></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;11:25:28</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>这个对话已被手动关闭。</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑><B>訪客</B> 已离开与 <B>Administrator</B> 的对话</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>','在线客服 Chat Transcript\r\n\r\nDate: Tue, 02 Aug 2011 11:25:39 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11734\r\n-------------------------------------------------------------\r\n| 02.08.2011 11:19:22 | Administrator: 欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？\r\n| 02.08.2011 11:19:37 | 訪客: zzZZ','','','ZH','CN','119.92.xxx.xxx.static.pldt.net','119.92.66.xxx',0,1,'','','a:0:{}'),(1312268480,1312269531,1312269536,'11735','400f2e1eb3','訪客','9baf6c1','Support','','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;15:01:02</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>对话请求 <B>客服部</B> (Administrator):<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=14></TD>\r\n<TD noWrap>名字:&nbsp;&nbsp;</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:&nbsp;&nbsp;</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑>对话已由 <B>Administrator</B> 接受。</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;15:01:11</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑><FONT color=#004080 size=2 face=微软雅黑>欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;15:01:32</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5>试试看能不能看到名字</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;15:01:53</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑></FONT><FONT color=#004080 face=微软雅黑>日，不能。为啥？</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;15:18:41</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>这个对话已被手动关闭。</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑><B>訪客</B> 已离开与 <B>Administrator</B> 的对话</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>','在线客服 Chat Transcript\r\n\r\nDate: Tue, 02 Aug 2011 15:18:56 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11735\r\n-------------------------------------------------------------\r\n| 02.08.2011 15:01:25 | Administrator: 欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？\r\n| 02.08.2011 15:01:40 | 訪客: 试试看能不能看到名字\r\n| 02.08.2011 15:02:02 | Administrator: 日，不能。为啥？','','','ZH','CN','119.92.xxx.xxx.static.pldt.net','119.92.66.xxx',0,1,'','','a:0:{}'),(1312270146,1312270230,1312270230,'11736','400f2e1eb3','訪客','9baf6c1','Support','','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;15:28:45</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>对话请求 <B>客服部</B> (Administrator):<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=14></TD>\r\n<TD noWrap>名字:&nbsp;&nbsp;</TD>\r\n<TD>訪客</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:&nbsp;&nbsp;</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑>对话已由 <B>Administrator</B> 接受。</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;15:28:57</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑><FONT color=#004080 size=2 face=微软雅黑>欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>訪客</TD>\r\n<TD class=FCMg3>&nbsp;15:29:17</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5>日，还是没名字。</TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;15:29:32</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>还是访客。。。fuck</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 face=微软雅黑>?</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCA><FONT color=#004080 face=微软雅黑>延时更厉害？？</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;15:30:20</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>这个对话已被手动关闭。</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑><B>訪客</B> 已离开与 <B>Administrator</B> 的对话</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>','在线客服 Chat Transcript\r\n\r\nDate: Tue, 02 Aug 2011 15:30:30 +0800\r\n-------------------------------------------------------------\r\nName: 訪客\r\n\r\nChat Reference Number: 11736\r\n-------------------------------------------------------------\r\n| 02.08.2011 15:29:07 | Administrator: 欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？\r\n| 02.08.2011 15:29:25 | 訪客: 日，还是没名字。\r\n| 02.08.2011 15:29:41 | Administrator: 还是访客。。。fuck\r\n| 02.08.2011 15:30:09 | Administrator: ?\r\n| 02.08.2011 15:30:14 | Administrator: 延时更厉害？？','','','ZH','CN','119.92.xxx.xxx.static.pldt.net','119.92.66.xxx',0,1,'','','a:0:{}'),(1312271073,1312271139,1312271141,'11737','400f2e1eb3','darkmoon','9baf6c1','Support','','<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;15:44:10</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>对话请求 <B>客服部</B> (Administrator):<BR>\r\n<DIV class=TCBB>\r\n<TABLE class=TCB cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=TCCL rowSpan=14></TD>\r\n<TD noWrap>名字:&nbsp;&nbsp;</TD>\r\n<TD>darkmoon</TD></TR>\r\n<TR>\r\n<TD noWrap>Email:&nbsp;&nbsp;</TD>\r\n<TD><A href=\"mailto:\" target=_self></A></TD></TR>\r\n<TR>\r\n<TD>公司:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR>\r\n<TR>\r\n<TD vAlign=top>问题:&nbsp;&nbsp;</TD>\r\n<TD></TD></TR></TBODY></TABLE></DIV></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑>对话已由 <B>Administrator</B> 接受。</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>Administrator</TD>\r\n<TD class=FCM3>&nbsp;15:44:25</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑><FONT color=#004080 size=2 face=微软雅黑>欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？</FONT></FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMg0></TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg2>darkmoon</TD>\r\n<TD class=FCMg3>&nbsp;15:44:58</TD>\r\n<TD class=FCMg1></TD>\r\n<TD class=FCMg4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5></TD></TR></TBODY></TABLE>\r\n<TABLE cellSpacing=4 cellPadding=4 width=\"100%\">\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCF cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCQF1></TD>\r\n<TD class=FCQF2></TD>\r\n<TD class=FCQF3></TD></TR>\r\n<TR>\r\n<TD class=FCQF4></TD>\r\n<TD class=FCQF5>\r\n<TABLE class=TCQM cellSpacing=3>\r\n<TBODY>\r\n<TR>\r\n<TD align=center>\r\n<TABLE cellSpacing=0 cellPadding=0 align=right>\r\n<TBODY>\r\n<TR>\r\n<TD><STRONG>&nbsp;offline_gray.jpg</STRONG></TD>\r\n<TD width=100><A class=FILEREQUESTLINK_ALLOW name=file_request_allow target=_self>接受</A></TD>\r\n<TD width=75><A class=FILEREQUESTLINK_REJECT name=file_request_reject target=_self>拒绝</A></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></TD>\r\n<TD class=FCQF6></TD></TR>\r\n<TR>\r\n<TD class=FCQF7></TD>\r\n<TD class=FCQF8></TD>\r\n<TD class=FCQF9></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=TCM cellSpacing=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCM0></TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM2>系统</TD>\r\n<TD class=FCM3>&nbsp;15:45:01</TD>\r\n<TD class=FCM1></TD>\r\n<TD class=FCM4></TD></TR></TBODY></TABLE></TD></TR>\r\n<TR>\r\n<TD class=FCM5><FONT color=#004080 size=2 face=微软雅黑>你允许了 <B>darkmoon</B> 上传 <B>offline_gray.jpg</B> 到服务器。文件一上传完成你就可以下载。</FONT></TD></TR></TBODY></TABLE></DIV>\r\n<DIV>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD>\r\n<TABLE class=FCCF cellSpacing=0 cellPadding=0>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCCF1></TD>\r\n<TD></TD>\r\n<TD class=FCCF2></TD></TR>\r\n<TR>\r\n<TD></TD>\r\n<TD>\r\n<TABLE>\r\n<TBODY>\r\n<TR>\r\n<TD class=FCMCB><FONT color=#004080 size=2 face=微软雅黑><B>darkmoon</B> 已离开与 <B>Administrator</B> 的对话</FONT></TD></TR></TBODY></TABLE></TD>\r\n<TD></TD>\r\n<TR>\r\n<TD class=FCCF3></TD>\r\n<TD></TD>\r\n<TD class=FCCF4></TD></TR></TBODY></TABLE></TD></TR></TBODY></TABLE></DIV>','在线客服 Chat Transcript\r\n\r\nDate: Tue, 02 Aug 2011 15:45:41 +0800\r\n-------------------------------------------------------------\r\nName: darkmoon\r\n\r\nChat Reference Number: 11737\r\n-------------------------------------------------------------\r\n| 02.08.2011 15:44:35 | Administrator: 欢迎使用 E8 在线客服，这里是 Administrator ，请问有什么可以帮您？\r\n| 02.08.2011 15:45:02 | darkmoon: FILE UPLOAD REQUEST (offline_gray.jpg / ACCEPTED)','','','ZH','CN','119.92.xxx.xxx.static.pldt.net','119.92.66.xxx',0,1,'','','a:0:{}'),(1313573011,0,0,'11742','','','','','SERVERPAGE','','','','','EN','US','','',0,0,'','','a:0:{}'),(1313574640,0,0,'11743','','','','','SERVERPAGE','','','','','EN','US','','',0,0,'','','a:0:{}'),(1315463775,0,0,'11744','','','','','SERVERPAGE','','','','','ZH','CN','','',0,0,'','','a:0:{}'),(1315466160,0,0,'11745','','','','','SERVERPAGE','','','','','ZH','CN','','',0,0,'','','a:0:{}'),(1315466900,0,0,'11746','','','','','SERVERPAGE','','','','','ZH','CN','','',0,0,'','','a:0:{}'),(1315471382,0,0,'11747','','','','','SERVERPAGE','','','','','ZH','CN','','',0,0,'','','a:0:{}');
/*!40000 ALTER TABLE `chat_archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_files`
--

DROP TABLE IF EXISTS `chat_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_files` (
  `id` varchar(64) COLLATE utf8_bin NOT NULL,
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `file_mask` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `file_id` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `chat_id` int(10) unsigned NOT NULL DEFAULT '0',
  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `error` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `permission` tinyint(1) NOT NULL DEFAULT '-1',
  `download` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`created`),
  KEY `visitor_id` (`visitor_id`),
  CONSTRAINT `chat_files_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_files`
--

LOCK TABLES `chat_files` WRITE;
/*!40000 ALTER TABLE `chat_files` DISABLE KEYS */;
INSERT INTO `chat_files` VALUES ('lzar_f26867563fb69dc0e997a22df9e8223d',1315473030,'ab20.png','536041ba21_f26867563fb69dc0e997a22df9e8223d','f26867563fb69dc0e997a22df9e8223d',11747,'536041ba21','d3a25a4e38','9baf6c1','0',2,1,0);
/*!40000 ALTER TABLE `chat_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_forwards`
--

DROP TABLE IF EXISTS `chat_forwards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_forwards` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `sender_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `target_operator_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `target_group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `chat_id` int(11) unsigned NOT NULL DEFAULT '0',
  `conversation` mediumtext COLLATE utf8_bin NOT NULL,
  `info_text` mediumtext COLLATE utf8_bin NOT NULL,
  `processed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `received` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `chat_forwards_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `visitor_chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_forwards`
--

LOCK TABLES `chat_forwards` WRITE;
/*!40000 ALTER TABLE `chat_forwards` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_forwards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_posts`
--

DROP TABLE IF EXISTS `chat_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_posts` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `chat_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `micro` int(11) unsigned NOT NULL DEFAULT '0',
  `sender` varchar(65) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver` varchar(65) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_group` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `text` mediumtext COLLATE utf8_bin NOT NULL,
  `translation` mediumtext COLLATE utf8_bin NOT NULL,
  `translation_iso` varchar(10) COLLATE utf8_bin NOT NULL DEFAULT '',
  `received` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `persistent` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_posts`
--

LOCK TABLES `chat_posts` WRITE;
/*!40000 ALTER TABLE `chat_posts` DISABLE KEYS */;
INSERT INTO `chat_posts` VALUES ('d62e4ddab389492aabd89309bdc19acf','11747',1315471439,89283400,'9baf6c1','536041ba21~d3a25a4e38','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">6.gif</a>&nbsp;','','',1,0),('4aa6a07e061a46fbbd7e16d40f814f31','11747',1315472393,65197300,'9baf6c1','536041ba21~d3a25a4e38','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('c48f23ab7fd141479d141e7c0bae6cd7','11747',1315471383,51746500,'9baf6c1','536041ba21~d3a25a4e38','','<FONT face=\"微软雅黑\" size=\"2\" color=\"#004080\">欢迎使用 E8 在线客服，我是 Paul ，请问有什么可以帮您？</FONT>','','',1,0),('79f9bd445d7e477898d9876757e696b9','11746',1315470454,62359600,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('e1e0bd9a99b344599593a77799c84800','11746',1315470483,87828400,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://sdsf.xoc\">title</a>&nbsp;','','',1,0),('21a0ac4a13e34fc0b2236b9e052f8882','11746',1315470905,42361700,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('4820dc5880fc43798b1b9978bc003c77','11746',1315470173,48136700,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('275723289bb349d1aa5c35bdfd2f1132','11746',1315470098,16539100,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('6cc7cdd821e1478188249d4d046a135d','11746',1315470020,53941100,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('ad0e03a6d5d249fb8cd9a63cafb007e3','11746',1315469970,25840200,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('0f8c7307a03e42c5995dd0d9f91d09eb','11746',1315469929,62904200,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('fb40a469567042db95cf82482d58ae79','11746',1315469873,24052100,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('e4b2305dadac4191953c3ff9c2fc5cd8','11746',1315469648,13489400,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('6fa2a6fa591a43fd93c8cddbf2f67738','11746',1315469133,30785300,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.gif</a>&nbsp;','','',1,0),('4af75a082f904b3b9c0fbd3848aceb2a','11746',1315468873,34658100,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('69057a926ee75598688c170c5b9e1a69','11746',1315466910,12949400,'536041ba21~e46c0600b2','9baf6c1','','8-)','','',1,0),('8174e0d3fcd04686857b748ba5b8112c','11746',1315467429,98934800,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">8.gif</a>&nbsp;','','',1,0),('bb211e9e6a4349e2ac40c90aa3dc3a70','11745',1315466354,47508800,'9baf6c1','536041ba21~6e7e8f27d9','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">5.gif</a>&nbsp;','','',1,0),('bffd064e8ffc4d98a92214f79b25fd00','11745',1315466445,61320500,'9baf6c1','536041ba21~6e7e8f27d9','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">10.gif</a>&nbsp;','','',1,0),('81c2cb27b0604c6aadf321dee3753f59','11744',1315463776,89061600,'9baf6c1','536041ba21~5a91a151f6','','<FONT face=\"微软雅黑\" size=\"2\" color=\"#004080\">欢迎使用 E8 在线客服，我是 Paul ，请问有什么可以帮您？</FONT>','','',1,0),('a9f38dec97b14cbcb0a1fc527dce3a83','11744',1315463806,16358100,'9baf6c1','536041ba21~5a91a151f6','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('fb83852a666540749ab942f4e64ff10e','11745',1315466162,27464400,'9baf6c1','536041ba21~6e7e8f27d9','','<FONT face=\"微软雅黑\" size=\"2\" color=\"#004080\">欢迎使用 E8 在线客服，我是 Paul ，请问有什么可以帮您？</FONT>','','',1,0),('d33d08a9820423a207cb1ece280935be','11745',1315466171,56832800,'536041ba21~6e7e8f27d9','9baf6c1','','zzZZ','','',1,0),('43e523d1465d40d8ae2bbc28fb69395e','11745',1315466187,82407000,'9baf6c1','536041ba21~6e7e8f27d9','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('ce269ae7d0ea4e3e9464850c0b6e07ed','11745',1315466291,81647400,'9baf6c1','536041ba21~6e7e8f27d9','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">1.jpg</a>&nbsp;','','',1,0),('2ec0c0f115be409aa0b735d0d4711209','11745',1315466524,19218200,'9baf6c1','536041ba21~6e7e8f27d9','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">11.gif</a>&nbsp;','','',1,0),('9e5a7263b7954d1eb347568ff1559862','11746',1315466900,81321200,'9baf6c1','536041ba21~e46c0600b2','','<FONT face=\"微软雅黑\" size=\"2\" color=\"#004080\">欢迎使用 E8 在线客服，我是 Paul ，请问有什么可以帮您？</FONT>','','',1,0),('568db906f215411889784cb091368184','11746',1315468609,73660800,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">5.gif</a>&nbsp;','','',1,0),('b03968889fad48e7aa08cb23a0d9b769','11746',1315468702,31321100,'9baf6c1','536041ba21~e46c0600b2','','<a target=\"_blank\" class=\"lz_chat_link\" style=\"font-family:微软雅黑;\" href=\"http://e8ocs.com/getfile.php?id=\">5.gif</a>&nbsp;','','',1,0);
/*!40000 ALTER TABLE `chat_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_requests`
--

DROP TABLE IF EXISTS `chat_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_requests` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `sender_system_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `sender_group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `event_action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `text` mediumtext COLLATE utf8_bin NOT NULL,
  `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `declined` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver_browser_id` (`receiver_browser_id`),
  CONSTRAINT `chat_requests_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_requests`
--

LOCK TABLES `chat_requests` WRITE;
/*!40000 ALTER TABLE `chat_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_action_internals`
--

DROP TABLE IF EXISTS `event_action_internals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_action_internals` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `trigger_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_action_internals`
--

LOCK TABLES `event_action_internals` WRITE;
/*!40000 ALTER TABLE `event_action_internals` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_action_internals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_action_overlays`
--

DROP TABLE IF EXISTS `event_action_overlays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_action_overlays` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `position` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',
  `speed` tinyint(1) NOT NULL DEFAULT '1',
  `slide` tinyint(1) NOT NULL DEFAULT '1',
  `margin_left` int(11) NOT NULL DEFAULT '0',
  `margin_top` int(11) NOT NULL DEFAULT '0',
  `margin_right` int(11) NOT NULL DEFAULT '0',
  `margin_bottom` int(11) NOT NULL DEFAULT '0',
  `style` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `close_on_click` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `action_id` (`action_id`),
  CONSTRAINT `event_action_overlays_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_action_overlays`
--

LOCK TABLES `event_action_overlays` WRITE;
/*!40000 ALTER TABLE `event_action_overlays` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_action_overlays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_action_receivers`
--

DROP TABLE IF EXISTS `event_action_receivers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_action_receivers` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `action_id` (`action_id`),
  CONSTRAINT `event_action_receivers_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_action_receivers`
--

LOCK TABLES `event_action_receivers` WRITE;
/*!40000 ALTER TABLE `event_action_receivers` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_action_receivers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_action_senders`
--

DROP TABLE IF EXISTS `event_action_senders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_action_senders` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `pid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `group_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `priority` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_action_senders`
--

LOCK TABLES `event_action_senders` WRITE;
/*!40000 ALTER TABLE `event_action_senders` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_action_senders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_action_website_pushs`
--

DROP TABLE IF EXISTS `event_action_website_pushs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_action_website_pushs` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `target_url` varchar(2056) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ask` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `action_id` (`action_id`),
  CONSTRAINT `event_action_website_pushs_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `event_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_action_website_pushs`
--

LOCK TABLES `event_action_website_pushs` WRITE;
/*!40000 ALTER TABLE `event_action_website_pushs` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_action_website_pushs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_actions`
--

DROP TABLE IF EXISTS `event_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_actions` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `eid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `value` mediumtext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`eid`),
  CONSTRAINT `event_actions_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_actions`
--

LOCK TABLES `event_actions` WRITE;
/*!40000 ALTER TABLE `event_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_funnels`
--

DROP TABLE IF EXISTS `event_funnels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_funnels` (
  `eid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `uid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ind` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`eid`,`uid`),
  KEY `uid` (`uid`),
  CONSTRAINT `event_funnels_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `event_urls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_funnels_ibfk_2` FOREIGN KEY (`eid`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_funnels`
--

LOCK TABLES `event_funnels` WRITE;
/*!40000 ALTER TABLE `event_funnels` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_funnels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_goals`
--

DROP TABLE IF EXISTS `event_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_goals` (
  `event_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `goal_id` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `prim` (`event_id`,`goal_id`),
  KEY `target_id` (`goal_id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `event_goals_ibfk_1` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `event_goals_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_goals`
--

LOCK TABLES `event_goals` WRITE;
/*!40000 ALTER TABLE `event_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_triggers`
--

DROP TABLE IF EXISTS `event_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_triggers` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `action_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `triggered` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver_user_id` (`receiver_user_id`),
  CONSTRAINT `event_triggers_ibfk_1` FOREIGN KEY (`receiver_user_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_triggers`
--

LOCK TABLES `event_triggers` WRITE;
/*!40000 ALTER TABLE `event_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_triggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_urls`
--

DROP TABLE IF EXISTS `event_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_urls` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `eid` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `url` varchar(2048) COLLATE utf8_bin NOT NULL DEFAULT '',
  `referrer` varchar(2048) COLLATE utf8_bin NOT NULL DEFAULT '',
  `time_on_site` int(10) unsigned NOT NULL DEFAULT '0',
  `blacklist` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `event_id` (`eid`),
  CONSTRAINT `event_urls_ibfk_1` FOREIGN KEY (`eid`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_urls`
--

LOCK TABLES `event_urls` WRITE;
/*!40000 ALTER TABLE `event_urls` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_urls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `name` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `creator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `edited` int(10) unsigned NOT NULL DEFAULT '0',
  `editor` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `pages_visited` int(10) unsigned NOT NULL DEFAULT '0',
  `time_on_site` int(10) unsigned NOT NULL DEFAULT '0',
  `max_trigger_amount` int(10) unsigned NOT NULL DEFAULT '0',
  `trigger_again_after` int(10) unsigned NOT NULL DEFAULT '0',
  `not_declined` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `not_accepted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `not_in_chat` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `priority` int(10) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `search_phrase` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filters` (
  `creator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `editor` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `edited` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',
  `expiredate` int(10) NOT NULL DEFAULT '0',
  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `reason` text COLLATE utf8_bin NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `exertion` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `languages` text COLLATE utf8_bin NOT NULL,
  `activeipaddress` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `activevisitorid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `activelanguage` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filters`
--

LOCK TABLES `filters` WRITE;
/*!40000 ALTER TABLE `filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goals`
--

DROP TABLE IF EXISTS `goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` text COLLATE utf8_bin NOT NULL,
  `conversion` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ind` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goals`
--

LOCK TABLES `goals` WRITE;
/*!40000 ALTER TABLE `goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `info`
--

DROP TABLE IF EXISTS `info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `info` (
  `version` varchar(15) COLLATE utf8_bin NOT NULL,
  `chat_id` int(11) unsigned NOT NULL DEFAULT '11700',
  `ticket_id` int(11) unsigned NOT NULL DEFAULT '11700',
  `gtspan` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `info`
--

LOCK TABLES `info` WRITE;
/*!40000 ALTER TABLE `info` DISABLE KEYS */;
INSERT INTO `info` VALUES ('3.3.2.2',11747,11704,0);
/*!40000 ALTER TABLE `info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator_logins`
--

DROP TABLE IF EXISTS `operator_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operator_logins` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ip` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `time` int(11) unsigned NOT NULL DEFAULT '0',
  `password` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_logins`
--

LOCK TABLES `operator_logins` WRITE;
/*!40000 ALTER TABLE `operator_logins` DISABLE KEYS */;
/*!40000 ALTER TABLE `operator_logins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operator_status`
--

DROP TABLE IF EXISTS `operator_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operator_status` (
  `time` int(11) unsigned NOT NULL,
  `confirmed` int(11) unsigned NOT NULL,
  `internal_id` varchar(15) COLLATE utf8_bin NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`time`,`internal_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operator_status`
--

LOCK TABLES `operator_status` WRITE;
/*!40000 ALTER TABLE `operator_status` DISABLE KEYS */;
INSERT INTO `operator_status` VALUES (1315496818,1315496818,'everyoneintern',2),(1315496818,1315496818,'9baf6c1',2),(1315496612,1315496818,'everyoneintern',0),(1315496612,1315496818,'9baf6c1',0),(1315483200,1315496612,'everyoneintern',2),(1315483200,1315496612,'9baf6c1',2),(1315479600,1315483200,'everyoneintern',2),(1315479600,1315483200,'9baf6c1',2),(1315476414,1315479600,'everyoneintern',2),(1315476414,1315479600,'9baf6c1',2),(1315476000,1315476414,'everyoneintern',0),(1315476000,1315476414,'9baf6c1',0),(1315475668,1315476000,'everyoneintern',0),(1315475668,1315476000,'9baf6c1',0),(1315474028,1315475668,'everyoneintern',2),(1315474028,1315475668,'9baf6c1',2),(1315472400,1315474028,'everyoneintern',0),(1315472400,1315474028,'9baf6c1',0),(1315471314,1315472400,'everyoneintern',0),(1315471314,1315472400,'9baf6c1',0),(1315471136,1315471314,'everyoneintern',2),(1315471136,1315471314,'9baf6c1',2),(1315468800,1315471136,'everyoneintern',0),(1315468800,1315471136,'9baf6c1',0),(1315468511,1315468800,'everyoneintern',0),(1315468511,1315468800,'9baf6c1',0),(1315467578,1315468511,'everyoneintern',3),(1315467578,1315468511,'9baf6c1',3),(1315465200,1315467578,'everyoneintern',0),(1315465200,1315467578,'9baf6c1',0),(1315463401,1315465200,'everyoneintern',0),(1315463401,1315465200,'9baf6c1',0),(1315463399,1315463401,'everyoneintern',2),(1315463399,1315463401,'9baf6c1',2);
/*!40000 ALTER TABLE `operator_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operators`
--

DROP TABLE IF EXISTS `operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `operators` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `login_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `first_active` int(10) unsigned NOT NULL DEFAULT '0',
  `last_active` int(10) unsigned NOT NULL DEFAULT '0',
  `password` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',
  `typing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `visitor_file_sizes` mediumtext COLLATE utf8_bin NOT NULL,
  `last_chat_allocation` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operators`
--

LOCK TABLES `operators` WRITE;
/*!40000 ALTER TABLE `operators` DISABLE KEYS */;
INSERT INTO `operators` VALUES ('72f9f8a','',1310887717,1310887796,'46f94c8de14fb36680850768ff1b7f2a',2,0,'203.82.42.174',0,'a:3:{s:10:\"7e1194a677\";a:2:{s:10:\"c5cf973e01\";s:2:\"65\";s:10:\"c57cfe95a0\";N;}s:10:\"f9442edf9f\";a:1:{s:10:\"8699d789a4\";s:2:\"f0\";}s:10:\"30170e3af2\";a:2:{s:10:\"8f313e1ff3\";s:2:\"26\";s:10:\"cf3f6d0773\";N;}}',1310887355),('9baf6c1','',1315496729,1315496818,'c5769e3620c3ee2bd879b16c905d948e',2,1,'203.82.42.xxx',0,'a:0:{}',0),('e133fc4','',1309881931,1309881972,'c5769e3620c3ee2bd879b16c905d948e',2,1,'203.82.42.134',0,'a:0:{}',0);
/*!40000 ALTER TABLE `operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `predefined`
--

DROP TABLE IF EXISTS `predefined`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `predefined` (
  `id` int(11) unsigned NOT NULL,
  `internal_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `group_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `lang_iso` varchar(5) COLLATE utf8_bin NOT NULL,
  `invitation_manual` mediumtext COLLATE utf8_bin NOT NULL,
  `invitation_auto` mediumtext COLLATE utf8_bin NOT NULL,
  `welcome` mediumtext COLLATE utf8_bin NOT NULL,
  `website_push_manual` mediumtext COLLATE utf8_bin NOT NULL,
  `website_push_auto` mediumtext COLLATE utf8_bin NOT NULL,
  `browser_ident` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `auto_welcome` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `editable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predefined`
--

LOCK TABLES `predefined` WRITE;
/*!40000 ALTER TABLE `predefined` DISABLE KEYS */;
INSERT INTO `predefined` VALUES (0,'','Support','ZH','','','欢迎使用 E8 在线客服，我是 %name% ，请问有什么可以帮您？','','',1,1,1,0);
/*!40000 ALTER TABLE `predefined` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profile_pictures`
--

DROP TABLE IF EXISTS `profile_pictures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profile_pictures` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `internal_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `webcam` tinyint(1) NOT NULL DEFAULT '0',
  `data` mediumtext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profile_pictures`
--

LOCK TABLES `profile_pictures` WRITE;
/*!40000 ALTER TABLE `profile_pictures` DISABLE KEYS */;
/*!40000 ALTER TABLE `profile_pictures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profiles`
--

DROP TABLE IF EXISTS `profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profiles` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `edited` int(11) NOT NULL DEFAULT '0',
  `first_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `last_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `phone` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `fax` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `street` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `zip` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `department` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `city` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `country` varchar(255) COLLATE utf8_bin NOT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `languages` varchar(1024) COLLATE utf8_bin NOT NULL DEFAULT '',
  `comments` longtext COLLATE utf8_bin NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profiles`
--

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;
INSERT INTO `profiles` VALUES ('e133fc4',1309442936,'','','','','','','','','','','',0,'','',0),('9baf6c1',1312192739,'','','','','','','','','','','',1,'Chinese (simplified)','',0);
/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ratings` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL,
  `time` int(11) unsigned NOT NULL,
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `internal_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `fullname` varchar(32) COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `company` varchar(50) COLLATE utf8_bin NOT NULL,
  `qualification` tinyint(1) unsigned NOT NULL,
  `politeness` tinyint(1) unsigned NOT NULL,
  `comment` varchar(400) COLLATE utf8_bin NOT NULL,
  `ip` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
INSERT INTO `ratings` VALUES ('1310885517_203.82.42.134',1310885517,'7e1194a677','root','訪客','','',5,5,'','203.82.42.134');
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL,
  `owner` varchar(15) COLLATE utf8_bin NOT NULL,
  `editor` varchar(15) COLLATE utf8_bin NOT NULL,
  `value` longtext COLLATE utf8_bin NOT NULL,
  `edited` int(11) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` int(11) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL,
  `discarded` tinyint(1) unsigned NOT NULL,
  `parentid` varchar(32) COLLATE utf8_bin NOT NULL,
  `rank` int(11) unsigned NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources`
--

LOCK TABLES `resources` WRITE;
/*!40000 ALTER TABLE `resources` DISABLE KEYS */;
INSERT INTO `resources` VALUES ('3','9baf6c1','9baf6c1','%%_Files_%%',1315473041,'%%_Files_%%',1310540215,0,0,'1',1,11),('5','9baf6c1','9baf6c1','%%_External_%%',1315473041,'%%_External_%%',1310540215,0,0,'3',2,14),('98e22796b1','9baf6c1','9baf6c1','Guest',1315471659,'Guest',1310975603,0,1,'5',3,5),('f26867563fb69dc0e997a22df9e8223d','9baf6c1','9baf6c1','536041ba21_f26867563fb69dc0e997a22df9e8223d',1315473041,'ab20.png',1315473041,4,0,'536041ba21',4,109),('536041ba21','9baf6c1','9baf6c1','訪客',1315473041,'訪客',1315473041,0,0,'5',3,6),('f06bf7e8ff9b5843319bfe7581b71ad3','9baf6c1','9baf6c1','98e22796b1_f06bf7e8ff9b5843319bfe7581b71ad3',1315471659,'ball.png',1313573035,4,1,'98e22796b1',4,5243),('400f2e1eb3','9baf6c1','9baf6c1','darkmoon',1315471657,'darkmoon',1312271711,0,1,'5',3,8),('bfd7749e3a42d3f8ae64182c27bd7499','9baf6c1','9baf6c1','400f2e1eb3_bfd7749e3a42d3f8ae64182c27bd7499',1315471654,'help-icon-speech-bubbles.png',1312271711,4,1,'400f2e1eb3',4,2382);
/*!40000 ALTER TABLE `resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs`
--

DROP TABLE IF EXISTS `stats_aggs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs` (
  `year` smallint(4) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  `sessions` int(10) unsigned NOT NULL DEFAULT '0',
  `visitors_unique` int(10) unsigned NOT NULL DEFAULT '0',
  `conversions` int(10) unsigned NOT NULL DEFAULT '0',
  `aggregated` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `chats_forwards` int(10) unsigned NOT NULL DEFAULT '0',
  `chats_posts_internal` int(10) unsigned NOT NULL DEFAULT '0',
  `chats_posts_external` int(10) unsigned NOT NULL DEFAULT '0',
  `avg_time_site` double unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs`
--

LOCK TABLES `stats_aggs` WRITE;
/*!40000 ALTER TABLE `stats_aggs` DISABLE KEYS */;
INSERT INTO `stats_aggs` VALUES (2011,8,2,1312421194,74442500,10,3,0,1,0,12,4,2689.5),(2011,8,0,1315379915,16366500,11,4,0,1,0,19,4,1313.875),(2011,0,0,1312278016,92359400,10,3,0,0,0,12,4,2689.5),(2011,8,4,1313572776,57363800,0,0,0,1,0,0,0,0),(2011,8,17,1313596803,86015500,1,1,0,1,0,7,0,2566),(2011,8,18,1315379914,48161600,0,0,0,1,0,0,0,0),(2011,9,7,1315463396,4602900,1,1,0,1,0,0,0,4309),(2011,9,0,1315379928,59961700,0,0,0,0,0,0,0,0),(2011,9,8,1315463400,13888900,0,0,0,0,0,27,2,0);
/*!40000 ALTER TABLE `stats_aggs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_availabilities`
--

DROP TABLE IF EXISTS `stats_aggs_availabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_availabilities` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hour` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `seconds` int(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`user_id`,`hour`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_availabilities`
--

LOCK TABLES `stats_aggs_availabilities` WRITE;
/*!40000 ALTER TABLE `stats_aggs_availabilities` DISABLE KEYS */;
INSERT INTO `stats_aggs_availabilities` VALUES (2011,8,2,17,'everyoneintern',0,433),(2011,8,2,16,'everyoneintern',0,168),(2011,8,2,15,'everyoneintern',0,2583),(2011,8,2,14,'everyoneintern',0,1361),(2011,8,2,11,'everyoneintern',0,479),(2011,8,2,17,'9baf6c1',0,433),(2011,8,2,16,'9baf6c1',0,168),(2011,8,2,15,'9baf6c1',0,2583),(2011,8,2,14,'9baf6c1',0,1361),(2011,8,2,11,'9baf6c1',0,479),(2011,8,4,10,'everyoneintern',0,197),(2011,8,4,10,'9baf6c1',0,197),(2011,8,17,17,'everyoneintern',0,2418),(2011,8,17,18,'everyoneintern',0,505),(2011,8,17,17,'9baf6c1',0,2418),(2011,8,17,18,'9baf6c1',0,505),(2011,8,18,9,'9baf6c1',0,3068),(2011,8,18,10,'9baf6c1',0,2894),(2011,8,18,11,'9baf6c1',0,125),(2011,8,18,9,'everyoneintern',0,3068),(2011,8,18,10,'everyoneintern',0,2894),(2011,8,18,11,'everyoneintern',0,125);
/*!40000 ALTER TABLE `stats_aggs_availabilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_browsers`
--

DROP TABLE IF EXISTS `stats_aggs_browsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_browsers` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `browser` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`browser`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_browsers`
--

LOCK TABLES `stats_aggs_browsers` WRITE;
/*!40000 ALTER TABLE `stats_aggs_browsers` DISABLE KEYS */;
INSERT INTO `stats_aggs_browsers` VALUES (2011,8,2,6,8),(2011,8,0,6,8),(2011,0,0,7,1),(2011,8,2,7,1),(2011,8,2,5,1),(2011,8,0,5,1),(2011,8,0,7,1),(2011,0,0,6,8),(2011,0,0,5,1),(2011,8,17,8,1),(2011,8,0,8,1),(2011,9,7,5,1);
/*!40000 ALTER TABLE `stats_aggs_browsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_chats`
--

DROP TABLE IF EXISTS `stats_aggs_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_chats` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hour` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `accepted` int(10) unsigned NOT NULL DEFAULT '0',
  `declined` int(10) unsigned NOT NULL DEFAULT '0',
  `avg_duration` double unsigned NOT NULL DEFAULT '0',
  `avg_waiting_time` double unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`user_id`,`hour`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_chats`
--

LOCK TABLES `stats_aggs_chats` WRITE;
/*!40000 ALTER TABLE `stats_aggs_chats` DISABLE KEYS */;
INSERT INTO `stats_aggs_chats` VALUES (2011,8,2,11,'9baf6c1',1,1,0,379,10),(2011,8,2,15,'9baf6c1',6,6,0,294,14.3333),(2011,8,2,16,'9baf6c1',1,1,0,1499,1),(2011,8,17,17,'9baf6c1',2,2,0,642,14);
/*!40000 ALTER TABLE `stats_aggs_chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_cities`
--

DROP TABLE IF EXISTS `stats_aggs_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_cities` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `city` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_cities`
--

LOCK TABLES `stats_aggs_cities` WRITE;
/*!40000 ALTER TABLE `stats_aggs_cities` DISABLE KEYS */;
INSERT INTO `stats_aggs_cities` VALUES (2011,8,2,2,10),(2011,8,0,2,11),(2011,0,0,2,10),(2011,8,17,2,1),(2011,9,7,2,1);
/*!40000 ALTER TABLE `stats_aggs_cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_countries`
--

DROP TABLE IF EXISTS `stats_aggs_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_countries` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_countries`
--

LOCK TABLES `stats_aggs_countries` WRITE;
/*!40000 ALTER TABLE `stats_aggs_countries` DISABLE KEYS */;
INSERT INTO `stats_aggs_countries` VALUES (2011,8,2,'CN',10),(2011,8,0,'CN',10),(2011,0,0,'CN',10),(2011,8,17,'US',1),(2011,8,0,'US',1),(2011,9,7,'CN',1);
/*!40000 ALTER TABLE `stats_aggs_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_crawlers`
--

DROP TABLE IF EXISTS `stats_aggs_crawlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_crawlers` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `crawler` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`crawler`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_crawlers`
--

LOCK TABLES `stats_aggs_crawlers` WRITE;
/*!40000 ALTER TABLE `stats_aggs_crawlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_aggs_crawlers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_domains`
--

DROP TABLE IF EXISTS `stats_aggs_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_domains` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `domain` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_domains`
--

LOCK TABLES `stats_aggs_domains` WRITE;
/*!40000 ALTER TABLE `stats_aggs_domains` DISABLE KEYS */;
INSERT INTO `stats_aggs_domains` VALUES (2011,8,2,9,24),(2011,8,0,9,24),(2011,0,0,9,24),(2011,8,2,8,1),(2011,8,0,8,2),(2011,0,0,8,1),(2011,8,17,8,1),(2011,9,7,8,1);
/*!40000 ALTER TABLE `stats_aggs_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_durations`
--

DROP TABLE IF EXISTS `stats_aggs_durations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_durations` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `duration` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`duration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_durations`
--

LOCK TABLES `stats_aggs_durations` WRITE;
/*!40000 ALTER TABLE `stats_aggs_durations` DISABLE KEYS */;
INSERT INTO `stats_aggs_durations` VALUES (2011,8,2,5,2),(2011,8,0,6,2),(2011,0,0,5,2),(2011,8,2,6,1),(2011,8,0,7,2),(2011,0,0,6,1),(2011,8,2,7,2),(2011,8,0,5,2),(2011,0,0,1,5),(2011,8,2,1,5),(2011,8,0,1,5),(2011,0,0,7,2),(2011,8,17,6,1),(2011,9,7,7,1);
/*!40000 ALTER TABLE `stats_aggs_durations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_goals`
--

DROP TABLE IF EXISTS `stats_aggs_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_goals` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `goal` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`goal`),
  KEY `target` (`goal`),
  CONSTRAINT `stats_aggs_goals_ibfk_1` FOREIGN KEY (`goal`) REFERENCES `goals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_goals`
--

LOCK TABLES `stats_aggs_goals` WRITE;
/*!40000 ALTER TABLE `stats_aggs_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_aggs_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_isps`
--

DROP TABLE IF EXISTS `stats_aggs_isps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_isps` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `isp` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`isp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_isps`
--

LOCK TABLES `stats_aggs_isps` WRITE;
/*!40000 ALTER TABLE `stats_aggs_isps` DISABLE KEYS */;
INSERT INTO `stats_aggs_isps` VALUES (2011,8,2,2,10),(2011,8,0,2,11),(2011,0,0,2,10),(2011,8,17,2,1),(2011,9,7,2,1);
/*!40000 ALTER TABLE `stats_aggs_isps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_isps_copy`
--

DROP TABLE IF EXISTS `stats_aggs_isps_copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_isps_copy` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `isp` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`isp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_isps_copy`
--

LOCK TABLES `stats_aggs_isps_copy` WRITE;
/*!40000 ALTER TABLE `stats_aggs_isps_copy` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_aggs_isps_copy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_languages`
--

DROP TABLE IF EXISTS `stats_aggs_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_languages` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `language` varchar(5) COLLATE utf8_bin NOT NULL,
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_languages`
--

LOCK TABLES `stats_aggs_languages` WRITE;
/*!40000 ALTER TABLE `stats_aggs_languages` DISABLE KEYS */;
INSERT INTO `stats_aggs_languages` VALUES (2011,8,2,'ZH',10),(2011,8,0,'ZH',10),(2011,0,0,'ZH',10),(2011,8,17,'EN',1),(2011,8,0,'EN',1),(2011,9,7,'ZH',1);
/*!40000 ALTER TABLE `stats_aggs_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_pages`
--

DROP TABLE IF EXISTS `stats_aggs_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_pages` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `url` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`url`),
  KEY `url_id` (`url`),
  CONSTRAINT `stats_aggs_pages_ibfk_1` FOREIGN KEY (`url`) REFERENCES `visitor_data_pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_pages`
--

LOCK TABLES `stats_aggs_pages` WRITE;
/*!40000 ALTER TABLE `stats_aggs_pages` DISABLE KEYS */;
INSERT INTO `stats_aggs_pages` VALUES (2011,0,0,108,1),(2011,0,0,112,1),(2011,0,0,114,1),(2011,0,0,116,1),(2011,0,0,118,19),(2011,0,0,124,1),(2011,0,0,138,1),(2011,8,0,108,2),(2011,8,0,112,1),(2011,8,0,114,1),(2011,8,0,116,1),(2011,8,0,118,19),(2011,8,0,124,1),(2011,8,0,138,1),(2011,8,2,108,1),(2011,8,2,112,1),(2011,8,2,114,1),(2011,8,2,116,1),(2011,8,2,118,19),(2011,8,2,124,1),(2011,8,2,138,1),(2011,8,17,108,1),(2011,9,7,108,1);
/*!40000 ALTER TABLE `stats_aggs_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_pages_entrance`
--

DROP TABLE IF EXISTS `stats_aggs_pages_entrance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_pages_entrance` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `url` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_pages_entrance`
--

LOCK TABLES `stats_aggs_pages_entrance` WRITE;
/*!40000 ALTER TABLE `stats_aggs_pages_entrance` DISABLE KEYS */;
INSERT INTO `stats_aggs_pages_entrance` VALUES (2011,0,0,108,1),(2011,0,0,112,1),(2011,0,0,114,1),(2011,0,0,116,1),(2011,0,0,118,19),(2011,0,0,124,1),(2011,0,0,138,1),(2011,8,0,108,2),(2011,8,0,112,1),(2011,8,0,114,1),(2011,8,0,116,1),(2011,8,0,118,19),(2011,8,0,124,1),(2011,8,0,138,1),(2011,8,2,108,1),(2011,8,2,112,1),(2011,8,2,114,1),(2011,8,2,116,1),(2011,8,2,118,19),(2011,8,2,124,1),(2011,8,2,138,1),(2011,8,17,108,1),(2011,9,7,108,1);
/*!40000 ALTER TABLE `stats_aggs_pages_entrance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_pages_exit`
--

DROP TABLE IF EXISTS `stats_aggs_pages_exit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_pages_exit` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `url` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_pages_exit`
--

LOCK TABLES `stats_aggs_pages_exit` WRITE;
/*!40000 ALTER TABLE `stats_aggs_pages_exit` DISABLE KEYS */;
INSERT INTO `stats_aggs_pages_exit` VALUES (2011,8,2,108,1),(2011,8,2,114,1),(2011,8,2,116,1),(2011,8,2,112,1),(2011,8,0,108,2),(2011,8,0,112,1),(2011,8,0,116,1),(2011,8,0,114,1),(2011,0,0,138,1),(2011,0,0,124,1),(2011,0,0,116,1),(2011,0,0,114,1),(2011,8,2,138,1),(2011,8,2,124,1),(2011,8,2,118,19),(2011,8,0,118,19),(2011,8,0,124,1),(2011,8,0,138,1),(2011,0,0,112,1),(2011,0,0,108,1),(2011,0,0,118,19),(2011,8,17,108,1),(2011,9,7,108,1);
/*!40000 ALTER TABLE `stats_aggs_pages_exit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_queries`
--

DROP TABLE IF EXISTS `stats_aggs_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_queries` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `query` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`query`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_queries`
--

LOCK TABLES `stats_aggs_queries` WRITE;
/*!40000 ALTER TABLE `stats_aggs_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_aggs_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_referrers`
--

DROP TABLE IF EXISTS `stats_aggs_referrers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_referrers` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `referrer` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_referrers`
--

LOCK TABLES `stats_aggs_referrers` WRITE;
/*!40000 ALTER TABLE `stats_aggs_referrers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_aggs_referrers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_regions`
--

DROP TABLE IF EXISTS `stats_aggs_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_regions` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `region` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`region`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_regions`
--

LOCK TABLES `stats_aggs_regions` WRITE;
/*!40000 ALTER TABLE `stats_aggs_regions` DISABLE KEYS */;
INSERT INTO `stats_aggs_regions` VALUES (2011,8,2,2,10),(2011,8,0,2,11),(2011,0,0,2,10),(2011,8,17,2,1),(2011,9,7,2,1);
/*!40000 ALTER TABLE `stats_aggs_regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_resolutions`
--

DROP TABLE IF EXISTS `stats_aggs_resolutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_resolutions` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `resolution` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`resolution`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_resolutions`
--

LOCK TABLES `stats_aggs_resolutions` WRITE;
/*!40000 ALTER TABLE `stats_aggs_resolutions` DISABLE KEYS */;
INSERT INTO `stats_aggs_resolutions` VALUES (2011,8,2,5,10),(2011,8,0,5,10),(2011,0,0,5,10),(2011,8,17,6,1),(2011,8,0,6,1),(2011,9,7,5,1);
/*!40000 ALTER TABLE `stats_aggs_resolutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_search_engines`
--

DROP TABLE IF EXISTS `stats_aggs_search_engines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_search_engines` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `domain` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_search_engines`
--

LOCK TABLES `stats_aggs_search_engines` WRITE;
/*!40000 ALTER TABLE `stats_aggs_search_engines` DISABLE KEYS */;
/*!40000 ALTER TABLE `stats_aggs_search_engines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_systems`
--

DROP TABLE IF EXISTS `stats_aggs_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_systems` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `system` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`system`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_systems`
--

LOCK TABLES `stats_aggs_systems` WRITE;
/*!40000 ALTER TABLE `stats_aggs_systems` DISABLE KEYS */;
INSERT INTO `stats_aggs_systems` VALUES (2011,8,2,4,10),(2011,8,0,4,11),(2011,0,0,4,10),(2011,8,17,4,1),(2011,9,7,4,1);
/*!40000 ALTER TABLE `stats_aggs_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_visitors`
--

DROP TABLE IF EXISTS `stats_aggs_visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_visitors` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hour` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `visitors_unique` int(10) unsigned NOT NULL DEFAULT '0',
  `page_impressions` int(10) unsigned NOT NULL DEFAULT '0',
  `visitors_recurring` int(10) unsigned NOT NULL DEFAULT '0',
  `bounces` int(10) unsigned NOT NULL DEFAULT '0',
  `search_engine` int(10) unsigned NOT NULL DEFAULT '0',
  `from_referrer` int(10) unsigned NOT NULL DEFAULT '0',
  `browser_instances` int(10) unsigned NOT NULL DEFAULT '0',
  `js` int(10) unsigned NOT NULL DEFAULT '0',
  `on_chat_page` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`hour`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_visitors`
--

LOCK TABLES `stats_aggs_visitors` WRITE;
/*!40000 ALTER TABLE `stats_aggs_visitors` DISABLE KEYS */;
INSERT INTO `stats_aggs_visitors` VALUES (2011,8,2,11,4,5,3,0,0,0,5,4,1),(2011,8,2,14,4,7,3,0,0,0,7,4,2),(2011,8,2,15,2,28,2,0,0,0,28,2,12),(2011,8,17,17,1,3,1,0,0,0,3,1,2),(2011,9,7,15,1,2,1,0,0,0,2,1,1);
/*!40000 ALTER TABLE `stats_aggs_visitors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stats_aggs_visits`
--

DROP TABLE IF EXISTS `stats_aggs_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_aggs_visits` (
  `year` smallint(5) unsigned NOT NULL DEFAULT '0',
  `month` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `day` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `visits` int(10) unsigned NOT NULL DEFAULT '0',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`year`,`month`,`day`,`visits`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_aggs_visits`
--

LOCK TABLES `stats_aggs_visits` WRITE;
/*!40000 ALTER TABLE `stats_aggs_visits` DISABLE KEYS */;
INSERT INTO `stats_aggs_visits` VALUES (2011,8,2,7,1),(2011,8,0,7,1),(2011,0,0,7,1),(2011,8,2,6,1),(2011,8,2,5,1),(2011,8,2,4,1),(2011,8,2,3,1),(2011,8,2,2,1),(2011,8,0,6,1),(2011,8,0,5,1),(2011,8,0,4,1),(2011,8,0,3,1),(2011,8,0,8,2),(2011,0,0,6,1),(2011,0,0,5,1),(2011,0,0,4,1),(2011,0,0,3,1),(2011,0,0,2,1),(2011,8,2,8,2),(2011,8,2,1,2),(2011,8,0,2,2),(2011,8,0,1,2),(2011,0,0,8,2),(2011,0,0,1,2),(2011,8,17,2,1),(2011,9,7,9,1);
/*!40000 ALTER TABLE `stats_aggs_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_customs`
--

DROP TABLE IF EXISTS `ticket_customs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_customs` (
  `ticket_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `custom_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`ticket_id`,`custom_id`),
  CONSTRAINT `ticket_customs_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_customs`
--

LOCK TABLES `ticket_customs` WRITE;
/*!40000 ALTER TABLE `ticket_customs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_customs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_editors`
--

DROP TABLE IF EXISTS `ticket_editors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_editors` (
  `ticket_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `internal_fullname` varchar(32) COLLATE utf8_bin NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ticket_id`),
  CONSTRAINT `ticket_editors_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_editors`
--

LOCK TABLES `ticket_editors` WRITE;
/*!40000 ALTER TABLE `ticket_editors` DISABLE KEYS */;
INSERT INTO `ticket_editors` VALUES ('11701','Administrator',2,1311131293),('11702','Paul',2,1315496652),('11703','Administrator',2,1312187833),('11704','Paul',2,1315463887);
/*!40000 ALTER TABLE `ticket_editors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_messages`
--

DROP TABLE IF EXISTS `ticket_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(11) unsigned NOT NULL,
  `ticket_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `text` mediumtext COLLATE utf8_bin NOT NULL,
  `fullname` varchar(32) COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `company` varchar(50) COLLATE utf8_bin NOT NULL,
  `ip` varchar(15) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`),
  CONSTRAINT `ticket_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_messages`
--

LOCK TABLES `ticket_messages` WRITE;
/*!40000 ALTER TABLE `ticket_messages` DISABLE KEYS */;
INSERT INTO `ticket_messages` VALUES (1,1311084326,'11701','','','','','203.82.42.174'),(2,1312186974,'11702','','訪客','','','119.92.66.xxx'),(3,1312187689,'11703','哦。看到了是啥？','訪客','','','119.92.66.xxx'),(4,1313573394,'11704','','Guest','','','203.208.27.xxx');
/*!40000 ALTER TABLE `ticket_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL,
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `target_group_id` varchar(32) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES ('11701','80d4673574','Support'),('11702','536041ba21','Support'),('11703','536041ba21','Support'),('11704','98e22796b1','Support');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_browser_urls`
--

DROP TABLE IF EXISTS `visitor_browser_urls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_browser_urls` (
  `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `entrance` int(10) unsigned NOT NULL DEFAULT '0',
  `referrer` int(10) unsigned NOT NULL DEFAULT '0',
  `url` int(10) unsigned NOT NULL DEFAULT '0',
  `params` text COLLATE utf8_bin NOT NULL,
  `untouched` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`entrance`,`browser_id`),
  KEY `browser_id` (`browser_id`),
  CONSTRAINT `visitor_browser_urls_ibfk_1` FOREIGN KEY (`browser_id`) REFERENCES `visitor_browsers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_browser_urls`
--

LOCK TABLES `visitor_browser_urls` WRITE;
/*!40000 ALTER TABLE `visitor_browser_urls` DISABLE KEYS */;
INSERT INTO `visitor_browser_urls` VALUES ('c2e122db4a',1315463754,107,108,'','http://e8ocs.com/'),('5a91a151f6',1315463756,107,110,'','http://e8ocs.com/chat.php'),('6e7e8f27d9',1315466147,107,110,'','http://e8ocs.com/chat.php'),('e46c0600b2',1315466888,107,110,'','http://e8ocs.com/chat.php'),('d3a25a4e38',1315471367,107,110,'','http://e8ocs.com/chat.php'),('f56da8d6b3',1315474438,107,208,'','file:///C:/Users/Floyd/AppData/Local/Temp/tmpB66D.html');
/*!40000 ALTER TABLE `visitor_browser_urls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_browsers`
--

DROP TABLE IF EXISTS `visitor_browsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_browsers` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `visit_id` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `last_active` int(10) unsigned NOT NULL DEFAULT '0',
  `last_update` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',
  `is_chat` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `query` int(10) unsigned NOT NULL DEFAULT '0',
  `fullname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `customs` text COLLATE utf8_bin NOT NULL,
  `url_entrance` int(10) unsigned NOT NULL DEFAULT '0',
  `url_exit` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `visit_id` (`visit_id`),
  CONSTRAINT `visitor_browsers_ibfk_1` FOREIGN KEY (`visit_id`) REFERENCES `visitors` (`visit_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_browsers`
--

LOCK TABLES `visitor_browsers` WRITE;
/*!40000 ALTER TABLE `visitor_browsers` DISABLE KEYS */;
INSERT INTO `visitor_browsers` VALUES ('5a91a151f6','536041ba21','6b4694b',1315463756,1315463841,'96',1,0,'訪客','','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',0,0),('6e7e8f27d9','536041ba21','6b4694b',1315466147,1315466801,'09',1,0,'訪客','','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',0,0),('c2e122db4a','536041ba21','6b4694b',1315463754,1315466695,'47',0,0,'Ց','','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',0,0),('d3a25a4e38','536041ba21','6b4694b',1315471367,1315474017,'9a',1,0,'訪客','','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',0,0),('e46c0600b2','536041ba21','6b4694b',1315466888,1315471134,'d8',1,0,'訪客','','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',0,0),('f56da8d6b3','f81694104d','a3e6cf8',1315474438,1315474438,'e8',0,0,'','','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',0,0);
/*!40000 ALTER TABLE `visitor_browsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_chat_operators`
--

DROP TABLE IF EXISTS `visitor_chat_operators`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_chat_operators` (
  `chat_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` varchar(32) COLLATE utf8_bin NOT NULL,
  `declined` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `dtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`chat_id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `visitor_chat_operators_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `visitor_chats` (`chat_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_chat_operators`
--

LOCK TABLES `visitor_chat_operators` WRITE;
/*!40000 ALTER TABLE `visitor_chat_operators` DISABLE KEYS */;
INSERT INTO `visitor_chat_operators` VALUES (11744,'9baf6c1',0,0),(11745,'9baf6c1',0,0),(11746,'9baf6c1',0,0),(11747,'9baf6c1',0,0);
/*!40000 ALTER TABLE `visitor_chat_operators` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_chats`
--

DROP TABLE IF EXISTS `visitor_chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_chats` (
  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `visit_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `chat_id` int(11) unsigned NOT NULL DEFAULT '0',
  `fullname` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `company` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `typing` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `waiting` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `area_code` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `first_active` int(10) unsigned NOT NULL DEFAULT '0',
  `last_active` int(10) unsigned NOT NULL DEFAULT '0',
  `qpenalty` int(10) unsigned NOT NULL DEFAULT '0',
  `request_operator` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `request_group` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `question` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `customs` text COLLATE utf8_bin NOT NULL,
  `allocated` int(11) unsigned NOT NULL DEFAULT '0',
  `internal_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `internal_closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `internal_declined` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `external_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `external_close` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `exit` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`visitor_id`,`browser_id`,`visit_id`,`chat_id`),
  KEY `chat_id` (`chat_id`),
  CONSTRAINT `visitor_chats_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_chats`
--

LOCK TABLES `visitor_chats` WRITE;
/*!40000 ALTER TABLE `visitor_chats` DISABLE KEYS */;
INSERT INTO `visitor_chats` VALUES ('536041ba21','5a91a151f6','6b4694b',11744,'訪客','','',2,0,0,'SERVERPAGE',1315463765,1315463961,0,'9baf6c1','Support','sf','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',1315463775,1,1,0,1,1,1315463963),('536041ba21','6e7e8f27d9','6b4694b',11745,'訪客','','',2,0,0,'SERVERPAGE',1315466149,1315466801,0,'9baf6c1','Support','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',1315466160,1,0,0,1,1,1315466806),('536041ba21','d3a25a4e38','6b4694b',11747,'訪客','','',2,0,0,'SERVERPAGE',1315471368,1315474017,0,'9baf6c1','Support','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',1315471382,1,1,0,1,1,1315474021),('536041ba21','e46c0600b2','6b4694b',11746,'訪客','','',2,0,0,'SERVERPAGE',1315466889,1315471134,0,'9baf6c1','Support','','a:10:{i:0;s:0:\"\";i:1;s:0:\"\";i:2;s:0:\"\";i:3;s:0:\"\";i:4;s:0:\"\";i:5;s:0:\"\";i:6;s:0:\"\";i:7;s:0:\"\";i:8;s:0:\"\";i:9;s:0:\"\";}',1315466900,1,1,0,1,1,1315471137);
/*!40000 ALTER TABLE `visitor_chats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_area_codes`
--

DROP TABLE IF EXISTS `visitor_data_area_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_area_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `area_code` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `area_code` (`area_code`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_area_codes`
--

LOCK TABLES `visitor_data_area_codes` WRITE;
/*!40000 ALTER TABLE `visitor_data_area_codes` DISABLE KEYS */;
INSERT INTO `visitor_data_area_codes` VALUES (3,''),(4,'SERVERPAGE');
/*!40000 ALTER TABLE `visitor_data_area_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_browsers`
--

DROP TABLE IF EXISTS `visitor_data_browsers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_browsers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `browser` varchar(255) COLLATE utf8_bin NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `browser` (`browser`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_browsers`
--

LOCK TABLES `visitor_data_browsers` WRITE;
/*!40000 ALTER TABLE `visitor_data_browsers` DISABLE KEYS */;
INSERT INTO `visitor_data_browsers` VALUES (5,'Chrome 1',0),(6,'Internet Explorer 7',0),(7,'Internet Explorer 9',0),(8,'FireFox 5',0);
/*!40000 ALTER TABLE `visitor_data_browsers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_cities`
--

DROP TABLE IF EXISTS `visitor_data_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `city` (`city`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_cities`
--

LOCK TABLES `visitor_data_cities` WRITE;
/*!40000 ALTER TABLE `visitor_data_cities` DISABLE KEYS */;
INSERT INTO `visitor_data_cities` VALUES (2,'');
/*!40000 ALTER TABLE `visitor_data_cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_crawlers`
--

DROP TABLE IF EXISTS `visitor_data_crawlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_crawlers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `crawler` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `crawler` (`crawler`),
  UNIQUE KEY `crawler_2` (`crawler`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_crawlers`
--

LOCK TABLES `visitor_data_crawlers` WRITE;
/*!40000 ALTER TABLE `visitor_data_crawlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitor_data_crawlers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_domains`
--

DROP TABLE IF EXISTS `visitor_data_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_domains` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `external` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `search` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_domains`
--

LOCK TABLES `visitor_data_domains` WRITE;
/*!40000 ALTER TABLE `visitor_data_domains` DISABLE KEYS */;
INSERT INTO `visitor_data_domains` VALUES (7,'',0,0),(8,'http://e8ocs.com',0,0),(9,'localhost',0,0);
/*!40000 ALTER TABLE `visitor_data_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_isps`
--

DROP TABLE IF EXISTS `visitor_data_isps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_isps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isp` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `isp` (`isp`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_isps`
--

LOCK TABLES `visitor_data_isps` WRITE;
/*!40000 ALTER TABLE `visitor_data_isps` DISABLE KEYS */;
INSERT INTO `visitor_data_isps` VALUES (2,'');
/*!40000 ALTER TABLE `visitor_data_isps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_pages`
--

DROP TABLE IF EXISTS `visitor_data_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` int(10) unsigned NOT NULL DEFAULT '0',
  `path` int(10) unsigned NOT NULL DEFAULT '0',
  `title` int(10) unsigned NOT NULL DEFAULT '0',
  `area_code` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ` (`domain`,`path`)
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_pages`
--

LOCK TABLES `visitor_data_pages` WRITE;
/*!40000 ALTER TABLE `visitor_data_pages` DISABLE KEYS */;
INSERT INTO `visitor_data_pages` VALUES (107,7,7,6,3),(108,8,8,7,4),(110,8,9,8,4),(112,9,10,9,3),(114,9,11,9,3),(116,9,12,9,3),(118,9,13,10,3),(124,9,14,9,3),(138,9,15,10,3),(208,9,16,9,3);
/*!40000 ALTER TABLE `visitor_data_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_paths`
--

DROP TABLE IF EXISTS `visitor_data_paths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_paths` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_paths`
--

LOCK TABLES `visitor_data_paths` WRITE;
/*!40000 ALTER TABLE `visitor_data_paths` DISABLE KEYS */;
INSERT INTO `visitor_data_paths` VALUES (7,''),(8,'/'),(9,'/chat.php'),(10,':/Users/Floyd/AppData/Local/Temp/tmp2346.html'),(11,':/Users/Floyd/AppData/Local/Temp/tmp1F8F.html'),(12,':/Users/Floyd/AppData/Local/Temp/tmpB5A8.html'),(13,':/Users/Floyd/AppData/Local/Temp/non4635.htm'),(14,':/Users/Floyd/AppData/Local/Temp/tmp643F.html'),(15,':/Users/Floyd/AppData/Local/Temp/non1142.htm'),(16,':/Users/Floyd/AppData/Local/Temp/tmpB66D.html');
/*!40000 ALTER TABLE `visitor_data_paths` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_queries`
--

DROP TABLE IF EXISTS `visitor_data_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_queries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `query` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `query` (`query`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_queries`
--

LOCK TABLES `visitor_data_queries` WRITE;
/*!40000 ALTER TABLE `visitor_data_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitor_data_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_regions`
--

DROP TABLE IF EXISTS `visitor_data_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `region` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `region` (`region`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_regions`
--

LOCK TABLES `visitor_data_regions` WRITE;
/*!40000 ALTER TABLE `visitor_data_regions` DISABLE KEYS */;
INSERT INTO `visitor_data_regions` VALUES (2,'');
/*!40000 ALTER TABLE `visitor_data_regions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_resolutions`
--

DROP TABLE IF EXISTS `visitor_data_resolutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_resolutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resolution` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `resolution` (`resolution`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_resolutions`
--

LOCK TABLES `visitor_data_resolutions` WRITE;
/*!40000 ALTER TABLE `visitor_data_resolutions` DISABLE KEYS */;
INSERT INTO `visitor_data_resolutions` VALUES (5,'1366 x 768 (32 Bit)'),(6,'1366 x 768 (24 Bit)');
/*!40000 ALTER TABLE `visitor_data_resolutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_systems`
--

DROP TABLE IF EXISTS `visitor_data_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_systems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `os` (`system`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_systems`
--

LOCK TABLES `visitor_data_systems` WRITE;
/*!40000 ALTER TABLE `visitor_data_systems` DISABLE KEYS */;
INSERT INTO `visitor_data_systems` VALUES (4,'Windows 7');
/*!40000 ALTER TABLE `visitor_data_systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_data_titles`
--

DROP TABLE IF EXISTS `visitor_data_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_data_titles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `confirmed` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_data_titles`
--

LOCK TABLES `visitor_data_titles` WRITE;
/*!40000 ALTER TABLE `visitor_data_titles` DISABLE KEYS */;
INSERT INTO `visitor_data_titles` VALUES (6,'',0),(7,'LiveZilla Server Page',4),(8,'在线客服',22),(9,'LiveZilla Server Admin (Preview)',5),(10,'New Document',20);
/*!40000 ALTER TABLE `visitor_data_titles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_goals`
--

DROP TABLE IF EXISTS `visitor_goals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitor_goals` (
  `visitor_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `goal_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `first_visit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`visitor_id`,`goal_id`),
  KEY `visitor_id` (`visitor_id`),
  KEY `target_id` (`goal_id`),
  CONSTRAINT `visitor_goals_ibfk_1` FOREIGN KEY (`visitor_id`) REFERENCES `visitors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_goals`
--

LOCK TABLES `visitor_goals` WRITE;
/*!40000 ALTER TABLE `visitor_goals` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitor_goals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitors`
--

DROP TABLE IF EXISTS `visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitors` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `entrance` int(10) unsigned NOT NULL DEFAULT '0',
  `last_active` int(10) unsigned NOT NULL DEFAULT '0',
  `host` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ip` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',
  `system` smallint(5) unsigned NOT NULL DEFAULT '0',
  `browser` smallint(5) unsigned NOT NULL DEFAULT '0',
  `visits` smallint(5) unsigned NOT NULL DEFAULT '0',
  `visit_id` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT '',
  `visit_latest` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `visit_last` int(10) unsigned NOT NULL DEFAULT '0',
  `resolution` smallint(5) unsigned NOT NULL DEFAULT '0',
  `language` varchar(5) COLLATE utf8_bin NOT NULL,
  `country` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT '',
  `city` smallint(5) unsigned NOT NULL DEFAULT '0',
  `region` smallint(5) unsigned NOT NULL DEFAULT '0',
  `isp` smallint(5) unsigned NOT NULL DEFAULT '0',
  `timezone` varchar(24) COLLATE utf8_bin NOT NULL DEFAULT '',
  `latitude` double NOT NULL DEFAULT '0',
  `longitude` double NOT NULL DEFAULT '0',
  `geo_result` int(10) unsigned NOT NULL DEFAULT '0',
  `js` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `signature` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`,`entrance`),
  UNIQUE KEY `visit_id` (`visit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitors`
--

LOCK TABLES `visitors` WRITE;
/*!40000 ALTER TABLE `visitors` DISABLE KEYS */;
INSERT INTO `visitors` VALUES ('536041ba21',1315463754,1315474022,'','203.208.27.xxx',4,5,10,'6b4694b',1,1315379915,5,'ZH','CN',2,2,2,'+08:00',-522,-522,7,1,'a880db1c6fead6705b490c7cfaa73acb'),('f81694104d',1315474438,1315474438,'','203.208.27.xxx',4,6,1,'a3e6cf8',1,1315474438,5,'ZH','CN',2,2,2,'+08:00',-522,-522,7,1,'38a1a33dba77bd4dd6bd45b7a652d37b');
/*!40000 ALTER TABLE `visitors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `website_pushs`
--

DROP TABLE IF EXISTS `website_pushs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `website_pushs` (
  `id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `created` int(10) unsigned NOT NULL DEFAULT '0',
  `sender_system_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_user_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `receiver_browser_id` varchar(32) COLLATE utf8_bin NOT NULL DEFAULT '',
  `text` mediumtext COLLATE utf8_bin NOT NULL,
  `ask` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `target_url` varchar(2048) COLLATE utf8_bin NOT NULL DEFAULT '',
  `displayed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `accepted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `declined` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `receiver_browser_id` (`receiver_browser_id`),
  CONSTRAINT `website_pushs_ibfk_1` FOREIGN KEY (`receiver_browser_id`) REFERENCES `visitor_browsers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_pushs`
--

LOCK TABLES `website_pushs` WRITE;
/*!40000 ALTER TABLE `website_pushs` DISABLE KEYS */;
/*!40000 ALTER TABLE `website_pushs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-09-09  9:27:39
