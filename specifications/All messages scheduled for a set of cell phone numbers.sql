-- This will return all the messages scheduled for a set of cell phones.

SELECT u.email, A.cell_phone, D.description as message, 

R.`name` as Remedy, R.descr as Remedy_description,

 group_concat(T.`time`) as times

from accounts as A 
	INNER JOIN user_account UA on UA.id_account = a.id
	INNER JOIN programming P on P.id_account = UA.id_account
	INNER JOIN descriptions D on P.id = D.id_programming
	INNER JOIN REMEDY as R on R.id = D.id_remedy	
	INNER JOIN TIMES as T on T.id_description = D.id
	INNER JOIN accounts as ac on ac.id = UA.id_account
	INNER JOIN users as u on u.id = ac.id_user
	
where A.cell_phone  in('4899182829') -- , '4891937766') -- example containing two phones

group by D.id





















-- D.dt_start,
-- A.cell_phone,
-- r.`name` rem,  
-- t.`time` as times, 
-- a.`name`, 
-- D.DESCRIPTION as Description


 -- FROM -- lembre_facil_prod.accounts A 
	-- INNER join PROGRAMMING P ON (P.id_subscriber = A.id_user) 
	-- INNER JOIN 
    --  descriptions D ON (D.id_programming = P.id)
	-- INNER JOIN times T ON (T.id_description = D.ID)
	-- INNER JOIN REMEDY R ON (R.id = D.id_remedy)
-- where cell_phone = '4891937766' -- '4899182829'

 -- group by pid -- cell_phone