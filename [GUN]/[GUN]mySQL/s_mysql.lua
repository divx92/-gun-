	
	function mysql_export()
	
	end
	
	function mysql_connect()
		mySQL = dbConnect ( "mysql", "dbname=iroleplay.pl;host=185.25.148.85;port=3306" ,"mta", "patr0" )
		if mySQL then
			outputDebugString ("mySQL connected", 3)
			return true
		else 
			return false 
		end
	end
	addCommandHandler ("sql", mysql_connect)
	
	function mysql_getuid(login)
		if type(login) == "string" then
			local player = dbQuery(mySQL, "SELECT email FROM forum_members WHERE members_l_username = '"..login.."' ")
			local result, num_affected_rows, last_insert_id = dbPoll(player, -1)
			for k, v in pairs(result) do
				for k,ID in pairs(v) do
					outputDebugString(tostring(ID))
					return true, ID
				end
			end
			
			if result == nil then
				outputDebugString("dbPoll result not ready yet")
			elseif result == false then
				local error_code, error_msg = num_affected_rows, last_insert_id
				outputConsole("dbPoll failed. Error code: "..tostring(error_code).." Error message: "..tostring(error_msg))
			else
				outputConsole("dbPoll succeeded. Number of affected rows: "..tostring(num_affected_rows).." Last insert id:"..tostring(last_insert_id))
			end
			
		end
	end
		
	function mysql_playerConnect(user, pass)
		login = "patr0"
		password = "ecdca4dee412d925915919067abd369b"
		if mySQL then
			player = dbQuery( mySQL, "SELECT email FROM forum_members WHERE members_l_username = '"..login.."' AND members_pass_hash = '"..tostring(password).."' ")
			
			local bool, ID = mysql_getuid(login)
			if ID then
				setElementData (user, "UID", ID)
			end
			
		end
	end
	addCommandHandler ("user", mysql_playerConnect)
		
	
