	local cam = {
	{1977.2264404297 , -977.78271484375 , 126.30809783936 , 1977.6353759766 , -978.650390625 , 126.02552032471 , 0 , 70},
	{1939.0523681641 , -862.92730712891 , 121.83540344238 , 1938.5218505859 , -863.7626953125 , 121.69161224365 , 0 , 70},
	{1880.6090087891 , -774.74127197266 , 146.71339416504 , 1879.6925048828 , -775.10314941406 , 146.54260253906 , 0 , 70} ,
	{1747.826171875 , -551.16461181641 , 106.50849914551 , 1746.8763427734 , -550.94128417969 , 106.28943634033 , 0 , 70 },
	{1697.1885986328 , -273.90618896484 , 109.76409912109 , 1696.2749023438 , -273.55422973633 , 109.56107330322 , 0 , 70} ,
	{1628.1657714844 , 34.099300384521 , 63.269500732422 , 1627.1929931641 , 34.267074584961 , 63.109497070313 , 0 , 70 },
	{1645.3226318359 , 360.79470825195 , 127.00910186768 , 1644.4188232422 , 360.63137817383 , 126.61344146729 , 0 , 70 },
	}

	call (getResourceFromName ("[GUN]blur"), "sw_blur_remote", true)
	
	local function login_camAnim(bool)
		if bool then
			local table_pos = 1
				function main_anim ()
						local now = getTickCount()
						local elapsedTime = now - anim.startTime
						local duration = anim.endTime - anim.startTime
						local progress = elapsedTime / duration
						local toCam_x, toCam_y, toCam_z, toCam_x1, toCam_y1, toCam_z1 = unpack (cam[table_pos])
						local x, y, z = interpolateBetween ( cCam_x, cCam_y, cCam_z,
						toCam_x, toCam_y, toCam_z, progress, "Linear")
						local x1, y1, z1 = interpolateBetween ( cCam_x1, cCam_y1, cCam_z1,
						toCam_x1, toCam_y1, toCam_z1, progress, "Linear")
						setCameraMatrix(x, y, z, x1,y2,z1)
						if now >= anim.endTime then
							if table_pos < #cam then
								cCam_x, cCam_y, cCam_z = getCameraMatrix (getLocalPlayer())
								table_pos =  table_pos + 1
								anim.startTime = getTickCount()
								anim.endTime = anim.startTime + 10000
								outputChatBox (table_pos)
								outputChatBox ("END")
							else
								login_camAnim (false)
							end
						end
				end
				anim = {}
				anim.startTime = getTickCount()
				anim.endTime = anim.startTime + 10000
				isCamAnim = true
				cCam_x, cCam_y, cCam_z = getCameraMatrix (getLocalPlayer())
				addEventHandler ("onClientRender", root, main_anim)
			elseif isCamAnim then
				removeEventHandler ("onClientRender", root, main_anim)
			end
	end
	login_camAnim(true)
	
	local x,y = guiGetScreenSize()
	local login = {
					window = {
					 {x/2-256, y/2-128, 512, 256, tocolor (10, 10, 10, 250)}, -- bg white
						 {x/2-256, y/2-108, 512, 155, tocolor (113, 40, 40, 40)}, -- bg
							
					 {x/2-130, y/2-88, 356, 32, tocolor (10, 10, 10, 250)}, -- login
					{x/2-130, y/2-8, 356,32, tocolor (10, 10, 10, 250)}, -- pass
					
					{x/2+130, y/2+88, 108,32, tocolor (113, 40, 40, 250)}, -- login
				--	 {x/2+130, y/2+8, 108,32, tocolor (113, 40, 40, 250)},
					
					 {x/2-226, y/2-88, 64,64, tocolor (10, 10, 10, 255)},
					},
				texts = {
				{"Account Name ", x/2-120, y/2-83, 356,32, tocolor (150, 150, 150, 250), 1.0, "default-bold"}, -- pass
				{"Password ", x/2-120, y/2-3, 356,32, tocolor (150, 150, 150, 250), 1.0, "default-bold"}, -- pass
				{"Login ",x/2+130, y/2+88, 108,32, tocolor (150, 150, 150, 250), 1.0, "default-bold"}, -- pass
				{"UID : N/A", x/2-226, y/2-12, 356,32, tocolor (150, 150, 150, 250), 1.0, "default-bold"}, -- pass
				}
	}
	
	function dx_drawLoginWindow()
		for k,v in pairs (login.window) do
			dxDrawRectangle (unpack(v))
		end
		for k,v in pairs (login.texts) do
			dxDrawText (unpack(v))
		end
	end
	addEventHandler ("onClientRender", root, dx_drawLoginWindow)