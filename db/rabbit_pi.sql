######
drop database if exists rabbit;
create database rabbit character set = `utf8` collate = `utf8_general_ci`;
grant all on rabbit.* to 'pi'@'localhost';
grant usage on rabbit.* to 'pi'@'localhost';

grant usage on rabbit.* to 'rabbit_user'@'localhost' identified by 'rabbit_pass';
grant select, update, insert, delete, execute on rabbit.* to 'rabbit_user'@'localhost';

flush privileges;

connect rabbit;

CREATE TABLE `rabbit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `serialNbr` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `fkTimeZoneID` int(11) unsigned NOT NULL,
  `wakeHour` char(2) DEFAULT NULL,
  `sleepHour` char(2) DEFAULT NULL,
  `weatherCode` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fkLanguageID` int(11) unsigned default 1,
  `fkButtonID` int(11) unsigned default null,
  `lastUpdate` timestamp default CURRENT_TIMESTAMP,
  `lastConnect` timestamp default 0,
  `lastIP` varchar(255) default null,
  `degrees` char(1) default 'f',
  `lastRequest` varchar(255) default null,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table `rabbit` add lastCommand varchar(255) default null;
alter table `rabbit` add fkBottomColorID int(11) unsigned default 7;  #note make sure 7 is valid in color table
alter table `rabbit` add fkIdleActionID int(11) unsigned default 1;   #make sure 1 is nothing  
alter table `rabbit` add weekendWakeHour char(2) default null COLLATE utf8_general_ci;
alter table rabbit alter column fkButtonID set default 1; 
alter table rabbit add lastForumVisit timestamp default 0 COLLATE utf8_general_ci; 
alter table rabbit add reboot int default 0 COLLATE utf8_general_ci;
alter table rabbit add follow varchar(255) default null;
alter table rabbit add version int default 0 COLLATE utf8_general_ci;
update rabbit set fkButtonID=1 where fkButtonID is null;  #default is no action
alter table rabbit add wavTimeout int default 13000 COLLATE utf8_general_ci;
alter table rabbit add clockType int default 0 COLLATE utf8_general_ci;

create table queue (
	id int unsigned not null auto_increment
 ,fkRabbitID int unsigned not null
 ,cmd varchar(1024) not null
 ,minute int not null
 ,sent tinyint default 0
 ,lastUpdate timestamp default current_timestamp
 ,primary key(id)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 collate=utf8_general_ci;

create table `color` (
 `id` int unsigned not null auto_increment
 ,`name` varchar(255)
 ,PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into color (name) values('Red');
insert into color (name) values('Green');
insert into color (name) values('Blue');
insert into color (name) values('Yellow');
insert into color (name) values('White');
insert into color (name) values('Teal');
insert into color (name) values('Violet');
insert into color (name) values('Random');
insert into color (name) values('Random with arcade sound');

update color set name = 'Random with arcade sound' where name = 'Random Arcade';

CREATE TABLE `function` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL DEFAULT '',
  `command` varchar(255) NOT NULL DEFAULT '',
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

alter table function add activeV1 int default 0 collate utf8_general_ci; 

insert into `function` (description,command,active,activeV1)
select 'Taichi','DOTAICHI',1,1;

insert into `function` (description,command,active)
select 'Random announcement','PLAYRANDOM',1;

insert into `function` (description,command,active,activeV1)
select 'Tell the time','TELLTIME',1,1;

insert into `function` (description,command,active)
select 'Weather forecast','WEATHER',1;

insert into `function` (description,command,active)
select 'PacMan Lights','PACMAN',1;

insert into `function` (description,command,active)
select 'Random arcade sound','RANDOMARCADE',1;

insert into `function` (description,command,active)
select 'Dow Jones Industrial Average','DOWJONES',1;

insert into `function` (description,command,active)
select 'Current temperature','CURRENT_TEMP',1;

insert into `function` (description,command,active)
select 'Random Star Trek sound','RANDOM_TREK',1;

insert into `function` (description,command,active)
select 'BBC World News headlines','BBC_WORLD_NEWS',1;

insert into `function` (description,command,active)
select 'Engadget headlines','ENGADGET_NEWS',1;

insert into `function` (description,command,active)
select 'RSS feed 1','RSS_1',1;

insert into `function` (description,command,active)
select 'RSS feed 2','RSS_2',1;

insert into `function` (description,command,active)
select 'RSS feed 3','RSS_3',1;

insert into `function` (description,command,active)
select 'Twitter Follow','TwitterFollow',1;

CREATE TABLE `schedule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `seq` int(11) not null default 0,
  `enabled` tinyint not null default 0,
  `fromHour` char(2) NOT NULL,
  `toHour` char(2) NOT NULL,
  `minute` char(2) NOT NULL,
  `fkFunctionID` int(11) NOT NULL,
  `fkRabbitID` int(11) unsigned NOT NULL,
  `lastUpdate` timestamp default CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (fkRabbitID) REFERENCES rabbit(id)
  on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table RFID (
  `id` int unsigned not null auto_increment
 ,`fkRabbitID` int unsigned not null
 ,`lastUpdate` timestamp default current_timestamp
 ,`tag` varchar(255)
 ,`fkFunctionID` int unsigned default 11 
 ,PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 collate=utf8_general_ci;

alter table RFID add description varchar(255) COLLATE utf8_general_ci;

CREATE TABLE `language` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into language
(name
,description
)
select 'uk'
      ,'english-uk';

insert into language
(name
,description
)
select 'us'
      ,'english-us';

insert into language
(name
,description
)
select 'es'
      ,'espanol-es';

insert into language
(name
,description
)
select 'de'
      ,'deutsch-de';

insert into language
(name
,description
)
select 'it'
      ,'italiano-it';
      
CREATE TABLE `timeZone` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


insert into timeZone (name) values('America/New_York');
insert into timeZone (name) values('America/Chicago');
insert into timeZone (name) values('America/Los_Angeles');
insert into timeZone (name) values('America/Phoenix');
insert into timeZone (name) values('America/Denver');
insert into timeZone (name) values('Australia/Sydney');
insert into timeZone (name) values('Europe/Moscow');
insert into timeZone (name) values('Europe/London');
insert into timeZone (name) values('Europe/Paris');
insert into timeZone (name) values('Europe/Madrid');
insert into timeZone (name) values('Europe/Berlin');
insert into timeZone (name) values('Europe/Dublin');
insert into timeZone (name) values('Europe/Stockholm');
insert into timeZone (name) values('Pacific/Honolulu');
insert into timeZone (name) values('Pacific/Auckland');
insert into timeZone (name) values('Atlantic/Bermuda');
insert into timeZone (name) values('Asia/Dubai');
insert into timeZone (name) values('Europe/Kiev');

CREATE TABLE `button` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `idleAction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL DEFAULT '',
  `command` varchar(255) NOT NULL DEFAULT '',
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

truncate table idleAction;

insert into `idleAction` (description,command,active)
select 'Nothing','',1;

insert into `idleAction` (description,command,active)
select 'PacMan Lights','PacMan',1;

insert into `idleAction` (description,command,active)
select 'PacMan Lights & Twitchy Ears','PacManTwitchy',1;

insert into `idleAction` (description,command,active)
select 'Twitchy Ears','Twitchy',1;

insert into `idleAction` (description,command,active)
select 'Weather Lights','WeatherLights',1;

insert into `idleAction` (description,command,active)
select 'Weather Lights & Twitchy Ears','WeatherTwitchy',1;

insert into `idleAction` (description,command,active)
select 'Cheerlights (V1 only)','Cheerlights',1;

update idleAction a
set a.description = 'Cheerlights'
where a.description = 'Cheerlights (V1 only)';

CREATE TABLE `buttonAction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL DEFAULT '',
  `command` varchar(255) NOT NULL DEFAULT '',
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 collate=utf8_general_ci;

create table RSS (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fkRabbitID` int(11) unsigned not null,
  `url` varchar(255) NOT NULL DEFAULT '',
  `active` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 collate=utf8_general_ci;

truncate table buttonAction;

insert into `buttonAction` (description,command,active)
select 'Nothing','',1
union
select 'Weather forecast','WeatherForecast',1
union
select 'Current temperature','CurrentTemp',1
union
select 'Dow Jones Industrial Average','DOWJONES',1
union
select 'Random announcement','PLAYRANDOM',1
union
select 'Random Star Trek sound','RANDOM_TREK',1
union
select 'BBC World News headlines','BBC_WORLD_NEWS',1
union
select 'Engadget headlines','ENGADGET_NEWS',1
union 
select 'Twitter Follow','TWITTER_FOLLOW',1
;

#############################################################################
# get rabbit
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetRabbit$$
create procedure sp_GetRabbit(in _serNbr char(12)
                             #,out msg varchar(255)
                             )
func:begin
  #this proc will get a rabbit
  
  declare _count int default 0;
  declare _rows int default 0;
	
  SELECT r.id
  		  ,r.name
  		  ,t.name
			  ,weatherCode
			  ,email
			  ,sleepHour
			  ,wakeHour
			  ,l.description as langDesc
			  ,r.degrees
			  ,r.lastRequest
			  ,c.name
			  ,i.description
			  ,r.weekendWakeHour
			  ,b.description
			  ,r.lastForumVisit
			  ,r.reboot
			  ,r.follow
			  ,r.version
			  ,r.wavTimeout
			  ,r.clockType
	from rabbit r
	inner join language l on l.id = r.fkLanguageID
	inner join timeZone t on t.id = r.fkTimeZoneID
	inner join color     c on c.id = r.fkBottomColorID
	inner join idleAction i on i.id = r.fkIdleActionID
	inner join buttonAction b on b.id = r.fkButtonID
	where serialNbr = _serNbr;
	
end$$
DELIMITER ;

#############################################################################
# get schedule
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetSchedule$$
create procedure sp_GetSchedule(in _rabbitID int unsigned
                               )
func:begin
  #this proc will get all schedules for a rabbit
  
	declare _count int default 0;
	declare _rows int default 0;
	
	#check for rabbit
	select count(*) into _count from rabbit where id = _rabbitID;
	
	if _count < 1 then
		select "Your rabbit was not found in the rabbit hutch." as msg;
		leave func;
	end if;
	
	select s.enabled     #into enabled
	      ,s.fromHour    #into fromHour
	      ,s.toHour      #into toHour
	      ,s.minute      #into minute
	      ,f.description #into description
	      ,s.seq
  		  ,'OK' as msg
  from schedule s
  inner join function f on f.id = s.fkFunctionID
  where s.fkRabbitID = _rabbitID
  order by seq;
  
	
end$$
DELIMITER ;

#############################################################################
# purge schedule
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_PurgeSchedule$$
create procedure sp_PurgeSchedule(in _serNbr char(12)
                                 ,out msg varchar(255)
                                 )
func:begin
  #this proc will purge all schedules for a rabbit
  
  declare _count int default 0;
  declare _rows int default 0;
	declare _rabbitID int default 0;
	
  #check for rabbit
	select count(*) into _count from rabbit where serialNbr = _serNbr;
	
	if _count < 1 then
		set msg = "Your rabbit was not found in the database.";
		leave func;
	end if;
	
	select id into _rabbitID from rabbit where serialNbr = _serNbr;
	
	#delete all schedules for this rabbit
	
	select count(*) into _count from schedule where fkRabbitID = _rabbitID;
	
	if _count > 0 then
		delete from schedule
		where fkRabbitID = _rabbitID;
	end if;

	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# purge rss
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_PurgeRSS$$
create procedure sp_PurgeRSS(in _serNbr char(12)
                            ,out msg varchar(255)
                             )
func:begin
  #this proc will purge all RSS entries for a rabbit
  
  declare _count int default 0;
  declare _rows int default 0;
	declare _rabbitID int default 0;
	
  #check for rabbit
	select count(*) into _count from rabbit where serialNbr = _serNbr;
	
	if _count < 1 then
		set msg = "Your rabbit was not found in the database.";
		leave func;
	end if;
	
	select id into _rabbitID from rabbit where serialNbr = _serNbr;
	
	#delete all RSS for this rabbit
	
	select count(*) into _count from RSS where fkRabbitID = _rabbitID;
	
	if _count > 0 then
		delete from RSS
		where fkRabbitID = _rabbitID;
	end if;

	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# get rabbit by ID
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetRabbitByID$$
create procedure sp_GetRabbitByID(in _id int unsigned
                             )
func:begin
  #this proc will get a rabbit
  
  declare _count int default 0;
  declare _rows int default 0;
	
  SELECT r.id
  		  ,r.name
  		  ,t.name
			  ,weatherCode
			  ,email
			  ,sleepHour
			  ,wakeHour
			  ,l.description as langDesc
			  ,r.degrees
			  ,r.lastRequest
			  ,c.name
			  ,i.description
			  ,r.weekendWakeHour
			  ,b.description
			  ,r.lastForumVisit
			  ,r.reboot
			  ,r.follow
			  ,r.version
			  ,r.wavTimeout
			  ,r.clockType
	from rabbit r
	inner join language l on l.id = r.fkLanguageID
	inner join timeZone t on t.id = r.fkTimeZoneID
	inner join color     c on c.id = r.fkBottomColorID
	inner join idleAction i on i.id = r.fkIdleActionID
	inner join buttonAction b on b.id = r.fkButtonID
	where id = _id;
	
end$$
DELIMITER ;

#############################################################################
# get RSS
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetRSS$$
create procedure sp_GetRSS(in _rabbitID int unsigned
                          )
func:begin
  #this proc will get all RSS for a rabbit
  
	declare _count int default 0;
	declare _rows int default 0;
	
	#check for rabbit
	select count(*) into _count from rabbit where id = _rabbitID;
	
	if _count < 1 then
		select "Your rabbit was not found in the rabbit hutch." as msg;
		leave func;
	end if;
	
	select s.active     
	      ,s.url         
	 		  ,'OK' as msg
  from RSS s
  where s.fkRabbitID = _rabbitID
  order by s.id;
  
	#set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# get rfid tag
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetRFID$$
create procedure sp_GetRFID(in _rabbitID int unsigned
                           )
func:begin
  #this proc will get the RFID tags and functions for a rabbit 
  
	declare _count int default 0;
	declare _rows int default 0;
	
	#check for rabbit
	select count(*) into _count from rabbit where id = _rabbitID;
	
	if _count < 1 then
		select "Your rabbit was not found in the rabbit hutch." as msg;
		leave func;
	end if;
	
	select r.tag
	      ,f.description as function
	      ,coalesce(r.description,'') as tag_desc
	from RFID r
	join function f on f.id = r.fkFunctionID
	where fkRabbitID = _rabbitID
	order by 1
	limit 10;
	
	#set msg = 'OK';
	
end$$
DELIMITER ;


#############################################################################
# new schedule
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_NewSchedule$$
create procedure sp_NewSchedule(in _serialNbr char(12)
                               ,in _enabled bit
                               ,in _from varchar(255)
                               ,in _to varchar(255)
                               ,in _minute varchar(255)
                               ,in _action varchar(255)
                               ,in _seq int unsigned
                               ,out msg varchar(255)
                               )
func:begin
  #this proc will add a new schedule
  
	declare _count int default 0;
	declare _rows int default 0;
	declare _rabbitID int unsigned default 0;
	
	#check for rabbit
	select count(*) into _count from rabbit where serialNbr = _serialNbr;
	
	if _count < 1 then
		set msg = "Your rabbit was not found in the rabbit hutch. Source NewSchedule.";
		leave func;
	end if;
	
	select id into _rabbitID from rabbit where serialNbr = _serialNbr;
	
	#check for existing schedule for this rabbit
	
	insert into schedule
	(enabled
	,fromHour
	,toHour
	,minute
	,fkFunctionID
	,fkRabbitID
	,seq
	)
	select _enabled
	      ,_from
	      ,_to
	      ,_minute
	      ,f.id
	      ,_rabbitID
	      ,_seq
	from function f
	where f.description = _action;
	 
	set _rows = row_count();
	
	if _rows < 1 then
		set msg = 'Insert failed.  No rows updated.';
		leave func;
	end if;
	
	set msg = 'OK';
	
end$$
DELIMITER ;


#############################################################################
# update rabbit
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_UpdateRabbit$$
create procedure sp_UpdateRabbit(in _oldSerialNbr char(12)
                                ,in _newSerialNbr char(12)
                                ,in _name varchar(255)
                                ,in _timeZone varchar(255)
                                ,in _weatherCode varchar(255)
                                ,in _email varchar(255)
                                ,in _wakeHour char(2)
                                ,in _sleepHour char(2)
                                ,in _language varchar(255)
                                ,in _temp char(1)
                                ,in _bottomColor varchar(255)
                                ,in _idleAction varchar(255)
                                ,in _weekendWakeHour char(2)
                                ,in _buttonAction varchar(255)
                                ,in _reboot int
                                ,in _follow varchar(255)
                                ,in _wavTimeout int
                                ,in _clockType int
                                ,out msg varchar(255)
                                )
func:begin
  #this proc will update an existing rabbit
  
	declare _count int default 0;
	declare _rows int default 0;
	
	#validate the parms
	if char_length(_newSerialNbr) < 12 then
		set msg = 'The serial number is too short. ';
		leave func;
	end if;
  
	#check for duplicate serial #
	
	select count(*) into @count  
  from rabbit r
	where r.serialNbr = _newSerialNbr
	  and r.id not in (select id from rabbit where serialNbr = _oldSerialNbr);
	
	if @count > 0 then
		set msg = 'Eeeks!  There is already a rabbit with that serial number!'; 
		leave func;
	end if;
	
	#check for duplicate name
	
	select count(*) into @count  
	from rabbit r
	where r.name = _name
	  and r.serialNbr <> _newSerialNbr;
	
	if @count > 0 then
		set msg = 'Eeeks!  There is already a rabbit with that name!'; 
		leave func;
	end if;
	
	#need to be able to change the ser #
	
	start transaction;
	
	update rabbit r
				,timeZone t
				,language l
				,color c
				,idleAction i
				,buttonAction b
	set r.serialNbr = _newSerialNBr
		 ,r.name = _name
		 ,r.fkTimeZoneID = t.id
		 ,r.weatherCode = _weatherCode
		 ,r.email = _email
		 ,r.wakeHour = _wakeHour
		 ,r.sleepHour = _sleepHour
		 ,r.fkLanguageID = l.id
		 ,r.lastUpdate = current_timestamp
		 ,r.degrees = _temp
		 ,r.fkBottomColorID = c.id
		 ,r.fkIdleActionID = i.id
		 ,r.weekendWakeHour = _weekendWakeHour
		 ,r.fkButtonID = b.id
		 ,r.reboot = _reboot
		 ,r.follow = _follow
		 ,r.wavTimeout = _wavTimeout
		 ,r.clockType = _clockType
	where t.name = _timeZone
	 and l.name = _language
	 and r.serialNbr = _oldSerialNbr
	 and c.name = _bottomColor
	 and i.description = _idleAction
	 and b.description = _buttonAction
	 ;
	 
	set @rows = row_count();
	
	if @rows < 1 then
	 set msg = 'Update rabbit failed.  No rows updated. '; 
	 rollback;
	 leave func;
	end if;
	
	if @rows > 1 then
	 set msg = 'Eeeks!  We found more than one rabbit!  Try updating the name and/or serial number in two steps.'; 
	 rollback;
	 leave func;
	end if;
	
	set msg = 'OK';
	commit;
	
end$$
DELIMITER ;

#############################################################################
# write to queue
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_Queue$$
create procedure sp_Queue(in _serNbr char(12)
                         ,in _min int(11)
                         ,in _cmd varchar(1024)
                         ,out msg varchar(255)
                          )
func:begin

	declare _rabbitID int default 0;
	
	#check for rabbit
	select id into _rabbitID from rabbit where serialNbr = _serNbr;
	
	if _rabbitID < 1 then
		set msg = "Your rabbit was not found in the database.";
		leave func;
	end if;
	
	insert into queue (fkRabbitID,cmd,minute) 
	select _rabbitID
	      ,_cmd
	      ,_min
	from dual  
	where not exists(select 1 from queue  
									 where fkRabbitID = _rabbitID 
										 and minute = _min 
										 and sent = 1);
	
	set msg = 'OK';
	
end$$
DELIMITER ;


#############################################################################
# Read queue
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetQueue$$
create procedure sp_GetQueue(in _id int unsigned
                            )
func:begin

	select id
	      ,cmd
	from queue
	where fkRabbitID = _id
	  and sent = 0
	order by id
	limit 1;
	
end$$
DELIMITER ;

#############################################################################
# Delete record from queue
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_DelQueue$$
create procedure sp_DelQueue(in _rabbitID int unsigned
                            )
func:begin

	delete 
	from queue
	where fkRabbitID = _rabbitID
	and time_to_sec(timediff(now(),lastUpdate)) > 180;
	
end$$
DELIMITER ;

#############################################################################
# Update record in queue to mark as sent
# Parms: _id - the ID of the queue table record to update
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_UpdQueue$$
create procedure sp_UpdQueue(in _id int unsigned
                            ,out msg varchar(255)
                          )
func:begin

	declare _rows int default 0;
	
	update queue
	set sent = 1
	where id = _id
	  and sent = 0;

    set _rows = row_count();
		
	if _rows < 1 then
		set msg = concat('sp_UpdQueue proc failed.  No rows updated. '); 
		leave func;
	end if;
		
	set msg = 'OK';
	
end$$
DELIMITER ;

#######################
# get rabbit count
#######################

DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetLatestRabbit$$
create procedure sp_GetLatestRabbit()
func:begin
  #this proc will get the name of the latest rabbit
  
  select name from rabbit where lastConnect <> '0000-00-00 00:00:00' order by id desc limit 1;
  
end$$
DELIMITER ;

#############################################################################
# get rabbit count
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetCount$$
create procedure sp_GetCount()
func:begin
  #this proc will get a count of rabbits
  
  select count(*) from rabbit;
  
end$$
DELIMITER ;

#############################################################################
# new rabbit
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_NewRabbit$$
create procedure sp_NewRabbit(
                              in _serialNbr char(12)
                             ,in _name varchar(255)
                             ,in _timeZone varchar(255)
                             ,in _weatherCode varchar(255)
                             ,in _email varchar(255)
                             ,in _wakeHour char(2)
                             ,in _sleepHour char(2)
                             ,in _language varchar(255)
                             ,in _temp char(1)
                             ,out msg varchar(255)
                             ,out rabbitID int
                             )
func:begin
  #this proc will add a new rabbit
  
	declare _count int default 0;
	declare _rows int default 0;
	declare _rabbitID int default -1;
	
	#check for unique name
	select count(*) into _count from rabbit where name = _name;
	if _count > 0 then
		set msg = concat('The name ',_name);
		set msg = concat(msg,' is already in use by another rabbit. ');
		leave func;
	end if;
	
	#validate the parms
	if char_length(_serialNbr) < 12 then
		set msg = 'The serial number is too short. ';
		leave func;
	end if;
    
	#check time zone
	select count(*) into _count from timeZone where name = _timeZone;
	if _count < 1 then
		set msg = concat(_timeZone,' is not a valid time zone. ');
		leave func;
	end if;
	
	#check for duplicate
	select count(*) into _count from rabbit where serialNbr = _serialNbr;
	if _count > 0 then
		set msg = 'Your rabbit is already in the rabbit hutch.  Please use the update rabbit feature to update your rabbit.';
		leave func;
	end if;
		
	insert into rabbit
	(serialNbr
	,name
	,fkTimeZoneID
	,weatherCode
	,email
	,wakeHour
	,sleepHour
	,fkLanguageID
	,degrees
	)
	select _serialNbr
				,_name
				,t.id
				,_weatherCode
				,_email
				,_wakeHour
				,_sleepHour
				,l.id
				,_temp
	from timeZone t
			,language l
	where t.name = _timeZone
		and l.name = _language;
	
	set _rows = row_count();
		
	if _rows < 1 then
		set msg = concat('New rabbit insert failed.  No rows updated. '); 
		leave func;
	end if;
	
	set rabbitID = @@IDENTITY;
	
	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# write to queue with rabbit ID
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_Queue2$$
create procedure sp_Queue2(in _rabbitID int unsigned
                          ,in _min int(11)
                          ,in _cmd varchar(1024)
                          ,out msg varchar(255)
                          )
func:begin

	declare _id int default 0;
	
	#check for rabbit
	select id into _id from rabbit where id = _rabbitID;
	
	if _id < 1 then
		set msg = "sp_Queue2: Your rabbit was not found in the database.";
		leave func;
	end if;
	
	insert into queue (fkRabbitID,cmd,minute) 
	select _rabbitID
	      ,_cmd
	      ,_min
	from dual  
	where not exists(select 1 from queue  
									 where fkRabbitID = _rabbitID 
										 and minute = _min 
										 and sent=1
										 );
	
	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# post last command
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_SetLastCommand$$
create procedure sp_SetLastCommand(in _rabbitID int unsigned
                                  ,in _lastCommand varchar(255)
                                  ,out msg varchar(255)
                                  )
func:begin
  #this proc will update last command sent to rabbit
  #check for rabbit
	
	if _rabbitID is null then
		set msg = "Null rabbitID received in set last command proc.";
		leave func;
	end if;
	
	update rabbit
	set lastCommand = _lastCommand
	where id = _rabbitID;
	  #and lastCommand <> _lastCommand; #first time is null so you cant do this
	
	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# check rabbit
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_CheckRabbit$$
create procedure sp_CheckRabbit(in _serNbr char(12)
                               #,out msg varchar(255)
                              )
func:begin
  #this proc will get a rabbits name and connect
  
  declare _count int default 0;
  declare _rows int default 0;
	
  SELECT r.name
  		  ,r.lastConnect
  		  ,current_timestamp
  from rabbit r
	where serialNbr = _serNbr;
	
end$$
DELIMITER ;

#############################################################################
# post to personal RSS
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_NewRSS$$
create procedure sp_NewRSS(in _serNbr char(12)
                           ,in _url varchar(1000)
                           ,in _active int(11)
                           ,out msg varchar(255)
                           )
func:begin
  #this proc will post to the RSS
  
  declare _count int default 0;
  declare _rows int default 0;
	declare _rabbitID int default 0;
	
  #check for rabbit
	select id into _rabbitID from rabbit where serialNbr = _serNbr;
	
	if _rabbitID < 1 then
		set msg = "Your rabbit was not found in the database.";
		leave func;
	end if;
	
	insert into RSS(fkRabbitID
	               ,url
	               ,active
	                 )
	values(_rabbitID
	      ,_url
	      ,_active
	      );
	
	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# purge queue
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_PurgeQueue$$
create procedure sp_PurgeQueue(in _serialNbr char(12)
                              ,out msg varchar(255)
                              )
func:begin

	declare _id int default 0;
	
	#check for rabbit
	select id into _id from rabbit where serialNbr = _serialNbr;
	
	if _id < 1 then
		set msg = "sp_PurgeQueue: Your rabbit was not found in the database.";
		leave func;
	end if;
	
	delete from queue
	where fkRabbitID = _id; 
	
	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# record rfid tag
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_CaptureRFID$$
create procedure sp_CaptureRFID(in _rabbitID int unsigned
                              , in _tag varchar(255)
                               )
func:begin
  #this proc will save the captured tag 
  
	declare _count int default 0;
	declare _rows int default 0;
	
	#check for rabbit
	select count(*) into _count from rabbit where id = _rabbitID;
	
	if _count < 1 then
		select "Your rabbit was not found in the rabbit hutch." as msg;
		leave func;
	end if;
	
	select count(*) into _count from RFID where fkRabbitID = _rabbitID and tag = _tag;
	
	if _count < 1 then
		insert into RFID(fkRabbitID
		                ,tag)
		values(_rabbitID, _tag);                
 	end if;
	
	#set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# save rfid tag assignment
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_SaveRFID$$
create procedure sp_SaveRFID(in _serNbr char(12)
                           , in _tag varchar(255)
                           , in _function varchar(255)
                           , in _description varchar(255)
                           , out msg varchar(255)
                               )
func:begin
  #this proc will save the captured tag 
  
	declare _count int default 0;
	declare _rows int default 0;
	declare _rabbitID int unsigned;
	declare _funcID int unsigned;
	
	#check for rabbit
	select id into _rabbitID from rabbit where serialNbr = _serNbr;
	
	if _rabbitID < 1 then
		set msg = "Your rabbit was not found in the database.";
		leave func;
	end if;
	
	#check function
	select id into _funcID from function where description = _function;
	
	if _funcID < 1 then
		set msg = concat(_function, " was not found in the function table.");
		leave func;
	end if;
	
	update RFID f
	      ,rabbit r
	set f.fkFunctionID = _funcID
	   ,f.description = _description
	where r.id = _rabbitID
	  and f.fkRabbitID = r.id
	  and f.tag = _tag;
	
	set msg = 'OK';
	
end$$
DELIMITER ;

#############################################################################
# update connect
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_LogConnect$$
create procedure sp_LogConnect(in _serNbr char(12)
                              ,in _ip varchar(255)
                              ,in _request varchar(255)
                              ,out rabbitID int unsigned
                              )
func:begin
  #this proc will update the connect

  select id into rabbitID from rabbit where serialNbr = _serNbr order by 1 limit 1;
  
  if rabbitID is null then 
  	set rabbitID = 0;
  	leave func;
  end if;
  
	update rabbit r
	set r.lastConnect = now()
	   ,r.lastIP = _ip
	   ,r.lastRequest = _request
	where serialNbr = _serNbr
	limit 1;
  
end$$
DELIMITER ;

#############################################################################
# Get eligible random color
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetRandomColor$$
create procedure sp_GetRandomColor()
func:begin
    #white is ugly, blue kills the LED for some reason in the real rabbit
            
    select name 
    from color 
    where name not in ('White','Random','Blue','Random with arcade sound') order by 1;
    
end$$
DELIMITER ;


#############################################################################
# Set rabbit version
# Parms: _version - pass 1 for V1, 2 for V2
#############################################################################
#use rabbit;
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_SetVersion$$
create procedure sp_SetVersion(in _rabbitID int unsigned
                              ,in _version int unsigned
                              )
func:begin
            
    update rabbit 
    set version = _version
    where id = _rabbitID
      and version = 0; 
 
end$$
DELIMITER ;

#############################################################################
# Get time zone.
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetTimeZone$$
create procedure sp_GetTimeZone()
func:begin

    SELECT name 
    from timeZone 
    order by 1;
   
end$$
DELIMITER ;

#############################################################################
# Get language.
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetLanguage$$
create procedure sp_GetLanguage()
func:begin

    SELECT name
          ,description 
          from language order by 1;

end$$
DELIMITER ;

#############################################################################
# Get rabbit dashboard.
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetRabbits$$
create procedure sp_GetRabbits()
func:begin

    select name
          ,lastConnect
          ,current_timestamp
          ,lastCommand 
    from rabbit  
    order by 2 desc;

end$$
DELIMITER ;
 
#############################################################################
# Get MySql runtime stats.
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetStats$$
create procedure sp_GetStats()
func:begin

    select variable_value as uptime 
    from information_schema.global_status 
    where variable_name='uptime';

end$$
DELIMITER ;
  

#############################################################################
# Get functions that rabbits can perform.
# Parms: _version - 1 for V1, 2 for V2 rabbits
#############################################################################
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_GetFunctions$$
create procedure sp_GetFunctions(in _version int unsigned)
func:begin

    if _version = 1 then
        SELECT description  
        from function  
        where activeV1=1 
        order by 1;
    else
        SELECT description  
        from function  
        where active=1 
        order by 1;
    end if;

end$$
DELIMITER ;
