#######################
use rabbit;
#######################
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
