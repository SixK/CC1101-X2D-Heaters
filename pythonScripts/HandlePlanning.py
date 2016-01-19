

class HandlePlanning :

	def __init__(self) :
		self._horaireLine = 2
		self._horairePos = 3
		self._zonePos=2
		self._dayPos=2
		self._zoneStateList=list()		
		self._horaireList=list()
		
	def setData(self, data) :
		self._data=data
		return self._data
		
	def getHoraires(self) :
		print ("getHoraires")
		self._horaireList=self._data[self._horaireLine-1][self._horairePos-1::]
		print (self._horaireList)
		return self._horaireList
	
	def getZone(self, zoneName) :
		x=0
		zoneFound=0
		for row in self._data:
			if(row[self._zonePos-1] == zoneName) :
				print ("Zone : %s found at pos : %d,%d"%(zoneName, self._zonePos, x))
				zoneFound=1
			else :
				print("Data : %s\n"%(row[self._zonePos-1]))
			x+=1
			
			if zoneFound :
				self._zoneStateList.append(row)
				
				if (row[self._zonePos-1] == "dimanche"):
					print ("last day found !")
					self._zoneStateList.append(row)
					break
	
	def getDays(self) :
		print ("getDays");
	
	def getTimeHoraire(self,time):
		x=0
		for horaire in self._horaireList:
			if horaire <= time and self._horaireList[x+1] >time :
				print self._horaireList[x]
				return x
				break
			x+=1
		
	def getStatus(self, day, time) :
		status=0
		print ("getStatus")
		hPos=self.getTimeHoraire(time)
	
		for line in self._zoneStateList :
			if line[self._dayPos -1] == day :
				status = line[self._dayPos + hPos]
				if status == '' :
					status = 0					
				if status == '1' :
					status = 1

				print status	
				break
				
		return status