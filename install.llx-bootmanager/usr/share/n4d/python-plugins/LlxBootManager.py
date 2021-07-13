import json
import os
import subprocess
import n4d.responses

class LlxBootManager:
	def __init__(self):
		self.cfgpath="/etc/llxbootmanager/bootcfg.json"
		# Cal canviar la ruta!!!
		self.php_path="/usr/share/llxbootmanager/www-boot"
		# Clients Boot Config
		self.clients_conf_path="/etc/llxbootmanager/clients.json"
		pass
	#def __init__

	def getBootList(self):
		'''
		Calculates and return all the options for the iPXE Boot Menu
		'''

		try:
			command=["php", self.php_path+"/getmenujson.php"]
			proc = subprocess.Popen(command,  stdout=subprocess.PIPE, cwd=self.php_path)
			out, err = proc.communicate()
			return n4d.responses.build_successful_call_response(json.loads(out))
			# return json.loads(out)
		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			#return -1

	def getBootOrder(self):
		'''
		Returns the boot order for iPE Boot Menu
		'''
		try:
			json_data=open(self.cfgpath)
			data=json.load(json_data)
			if not isinstance(data,dict) or 'bootorder' not in data:
				return n4d.responses.build_failed_call_response(ret_msg='Wrong content into '+self.cfgpath)	
			return n4d.responses.build_successful_call_response(data["bootorder"])
			#return data["bootorder"]
		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			#return -1

	def pushToBootList(self, label):
		'''
		Adds new label to Boot order for iPE Boot Menu
		'''
		
		try:
			# print "label is "+label
			json_data=open(self.cfgpath)
			data=json.load(json_data)
			if not isinstance(data,dict) or 'bootorder' not in data:
				return n4d.responses.build_failed_call_response(ret_msg='Wrong content into '+self.cfgpath)	

			# Removing label before push it
			data['bootorder']=list(filter(lambda a: a !=label.encode("utf-8"), data['bootorder']))
			# Cleaning
			data['bootorder']=list(filter(None, data['bootorder']))

			# Push label
			data["bootorder"].append(label)
			return n4d.responses.build_successful_call_response(self.setBootOrder(*data["bootorder"]))
			# return (self.setBootOrder(*data["bootorder"]));

		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			# return -1
		
	def removeFromBootList(self, label):
		'''
		Adds new label to Boot order for iPE Boot Menu
		'''
		try:
			# print "label is "+label
			json_data=open(self.cfgpath)
			data=json.load(json_data)
			if not isinstance(data,dict) or 'bootorder' not in data:
				return n4d.responses.build_failed_call_response(ret_msg='Wrong content into '+self.cfgpath)	

			# Removing label occurences
			data['bootorder']=list(filter(lambda a: a !=label.encode("utf-8"), data['bootorder']))
			# Cleaning
			data['bootorder']=list(filter(None, data['bootorder']))

			return n4d.responses.build_successful_call_response(self.setBootOrder(*data["bootorder"]))
			# return (self.setBootOrder(*data["bootorder"]));

		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			# return -1

	def prependBootList(self, label):
		'''
		Appending Boot List with label and returns the boot order for iPE Boot Menu
		'''
		try:
			print("[LlxBootManager] Prepending label "+label+" to Boot List.")
			json_data=open(self.cfgpath)
			data=json.load(json_data)
			if not isinstance(data,dict) or 'bootorder' not in data:
				return n4d.responses.build_failed_call_response(ret_msg='Wrong content into '+self.cfgpath)	

			# Removing label before push it
			data['bootorder']=list(filter(lambda a: a !=label.encode("utf-8"), data['bootorder']))

			data["bootorder"].insert(0,label)
			return n4d.responses.build_successful_call_response(self.setBootOrder(*data["bootorder"]))
			# return (self.setBootOrder(*data["bootorder"]))

		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			# return -1

	def getBootTimer(self):
		'''
		Returns menu time for PXE Menu
		'''
		try:
			json_data=open(self.cfgpath);
			data=json.load(json_data);
			if not isinstance(data,dict) or 'bootorder' not in data or 'timeout' not in data:
				return n4d.responses.build_failed_call_response(ret_msg='Wrong content for '+self.cfgpath)
			return n4d.responses.build_successful_call_response(data['timeout'])
			# return data["timeout"]
		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			# return -1

	def setBootOrder(self, *order):
		'''
		Set Boot order order for iPE Boot Menu
		'''
		try:
			time=self.getBootTimer();
			if time.get('status') != 0:
				return n4d.responses.build_failed_call_response(ret_msg='Fail calling getBootTimer')
			else:
				time = time.get('return')
			order=list(filter(None, order))

			bootcfg= { "bootorder": order, "timeout": time }

			bootcfg_string = json.dumps(bootcfg,indent=4,ensure_ascii=False)

			f = open(self.cfgpath,'w')
			f.writelines(bootcfg_string)
			f.close()

			return n4d.responses.build_successful_call_response()
		except Exception as e:
			print("Exception: "+str(e))
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			# return -1
		pass

	def setBootTimer(self, time):
		'''
		Set timeout for pxe menu
		'''
		if isinstance(time,str) and not str.isnumeric(time):
			return n4d.responses.build_failed_call_response(ret_msg='Wrong time parameter')
		elif isinstance(time,int):
			time = str(time)
		
		bootorder=self.getBootOrder();
		if bootorder.get('status') != 0:
			return n4d.responses.build_failed_call_response(ret_msg='Fail calling getBootOrder')
		else:
			bootorder = bootorder.get('return')
		bootcfg= { "bootorder": bootorder, "timeout": time }

		bootcfg_string = json.dumps(bootcfg,indent=4,ensure_ascii=False)

		f = open(self.cfgpath,'w')
		f.writelines(bootcfg_string)
		f.close()
		
		return n4d.responses.build_successful_call_response()


	# Methods to configure boot clients

	def getClientsConfig(self):
		'''
		Returns content for /etc/llxbootmanager/clients.json
		'''
		try:
			f = open(self.clients_conf_path,'r');
			data = (json.load(f));
			f.close();
			return n4d.responses.build_successful_call_response(json.dumps(data));
		except Exception as e:
			return n4d.responses.build_failed_call_response(str(e))
			# return False
		return n4d.responses.build_successful_call_response()

	def getClientConfig(self, mac):
		'''
		Return boot for an specific mac
		'''
		try:
			f=open(self.clients_conf_path, 'r')
			clients = (json.load(f))
			f.close()

			for cl in clients["clients"]:
				if (cl["mac"]==mac):
					return n4d.responses.build_successful_call_response(cl['boot'])
					# return cl["boot"]

			# if not found...
			# return False
			return n4d.responses.build_successful_call_response(False)	
		except Exception as e:
			return n4d.responses.build_failed_call_response(ret_msg=str(e))
			#return False

	def setClientConfig(self, *args):
		'''
		configures boot for certain mac. args[0] specifies mac, and args[1] specifies boot
		if boot (args[1]) is unspecified, removes it from config
		'''

		if(len(args)!=1 and len(args)!=2):
			return n4d.responses.build_invalid_arguments_response(-1)
			# return False;

		# Reading config file
		try:
			f = open(self.clients_conf_path,'r');
			clients = (json.load(f));
			f.close();
		except Exception as e:
			# File does not exists. Create an empty json...
			clients={"clients":[]}

		if(len(args)>=1):
			# Remove item if exists. It runs if we want to delete an item or modify any existent
			mac=args[0]
			for client in clients["clients"]:
				#print "compare: "+client["mac"]+" amb "+mac
				if (client["mac"]==mac):
					#print "FOUND"
					clients["clients"].remove(client)

		if(len(args)==2): # If we are specifying any boot option, let's add it
			boot=args[1]

			newclient={"mac":mac, "boot":boot}
			clients["clients"].append(newclient);

		# Finally Save Results
		with open(self.clients_conf_path, 'w') as f:
			json.dump(clients, f)

		return n4d.responses.build_successful_call_response()
