
local scx, scy = guiGetScreenSize()


Settings = {}
Settings.var = {}
Settings.var.brightness = 1.5
Settings.var.bloom = 1
Settings.var.blendR = 255
Settings.var.blendG = 255
Settings.var.blendB = 255
Settings.var.blendA = 255

	function sw_blur_remote(bool)
		if bool then
			createShader()
			addEventHandler( "onClientHUDRender", root,	blur_render)
		else
			if isElement (blurHShader) and isElement (blurVShader) then
				destroyElement (blurVShader)
				destroyElement (blurHShader)
			end
			removeEventHandler( "onClientHUDRender", root,	blur_render)
		end
	end
	
	function createShader()

		if getVersion ().sortable < "1.1.0" then
		--	outputChatBox( "Resource is not compatible with this client." )
			return
		end
        myScreenSource = dxCreateScreenSource( scx/Settings.var.bloom, scy/Settings.var.bloom )

        blurHShader,tecName = dxCreateShader( "blurH.fx" )

        blurVShader,tecName = dxCreateShader( "blurV.fx" )
		bAllValid = myScreenSource and blurHShader and blurVShader

		if not bAllValid then
		--	outputChatBox( "Could not create some things. Please use debugscript 3" )
		end
	end

    function blur_render()
		if not Settings.var then
			return
		end
        if bAllValid then
			RTPool.frameStart()
			dxUpdateScreenSource( myScreenSource, true )
			local current = myScreenSource
			current = applyDownsample( current )
			current = applyDownsample( current )
			current = applyGBlurH( current, Settings.var.brightness )
			current = applyGBlurV( current, Settings.var.brightness )
			dxSetRenderTarget()
			if current then
				dxSetShaderValue( blurVShader, "TEX0", current )
				local col = tocolor(Settings.var.blendR, Settings.var.blendG, Settings.var.blendB, Settings.var.blendA)
				dxDrawImage( 0, 0, scx, scy, current, 0,0,0, col )
			end
        end
    end


function applyDownsample( Src, amount )
	if not Src then return nil end
	amount = amount or 2
	local mx,my = dxGetMaterialSize( Src )
	mx = mx / amount
	my = my / amount
	local newRT = RTPool.GetUnused(mx,my)
	if not newRT then return nil end
	dxSetRenderTarget( newRT )
	dxDrawImage( 0, 0, mx, my, Src )
	return newRT
end

function applyGBlurH( Src, bloom )
	if not Src then return nil end
	local mx,my = dxGetMaterialSize( Src )
	local newRT = RTPool.GetUnused(mx,my)
	if not newRT then return nil end
	dxSetRenderTarget( newRT, true ) 
	dxSetShaderValue( blurHShader, "TEX0", Src )
	dxSetShaderValue( blurHShader, "TEX0SIZE", mx,my )
	dxSetShaderValue( blurHShader, "BLOOM", bloom )
	dxDrawImage( 0, 0, mx, my, blurHShader )
	return newRT
end

function applyGBlurV( Src, bloom )
	if not Src then return nil end
	local mx,my = dxGetMaterialSize( Src )
	local newRT = RTPool.GetUnused(mx,my)
	if not newRT then return nil end
	dxSetRenderTarget( newRT, true ) 
	dxSetShaderValue( blurVShader, "TEX0", Src )
	dxSetShaderValue( blurVShader, "TEX0SIZE", mx,my )
	dxSetShaderValue( blurVShader, "BLOOM", bloom )
	dxDrawImage( 0, 0, mx,my, blurVShader )
	return newRT
end


RTPool = {}
RTPool.list = {}

function RTPool.frameStart()
	for rt,info in pairs(RTPool.list) do
		info.bInUse = false
	end
end

function RTPool.GetUnused( mx, my )
	for rt,info in pairs(RTPool.list) do
		if not info.bInUse and info.mx == mx and info.my == my then
			info.bInUse = true
			return rt
		end
	end
	local rt = dxCreateRenderTarget( mx, my )
	if rt then
		RTPool.list[rt] = { bInUse = true, mx = mx, my = my }
	end
	return rt
end



local shaderList = {}

local removables = {
	'radar*',
	'*icon',
	'font*',
	'wjet6',
	'bullethitsmoke',
	'headlight',
	'vehiclegeneric256',
}

function world_init()

	local testShader, tec = dxCreateShader('night.fx')
	if not testShader then
	else
		for c=65,96 do
			local clone = dxCreateShader('night.fx')
			engineApplyShaderToWorldTexture(clone, string.format('*', c + 32))
			for i,v in pairs(removables) do
				engineRemoveShaderFromWorldTexture(clone, v)
			end
			table.insert(shaderList, clone)
		end
	end

	addEventHandler('onClientHUDRender', root, night_render)
end

function night_render()
	local int, dim = getElementInterior(localPlayer), getElementDimension(localPlayer)
	for _,shader in ipairs(shaderList) do
			dxSetShaderValue(shader, 'NIGHT', 0.1, 0.1, 0.1)
	end
end

world_init()
