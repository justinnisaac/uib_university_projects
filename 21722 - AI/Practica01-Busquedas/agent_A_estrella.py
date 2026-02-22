from queue import PriorityQueue
from practica import joc
from practica.estat import Estat
import time

# Agente que implementa búsqueda A*
class Viatger(joc.Viatger):
    def __init__(self, *args, **kwargs):
        super(Viatger, self).__init__(*args, **kwargs)
        self.__frontera = None # cola de estados pendientes por explorar
        self.__tancats = None # cola de estados ya visitados
        self.__cami_exit = None # plan (lista de (acción, dirección)) cuando se halle la meta

    def cerca(self, estat_inicial: Estat) -> bool:
        # Control
        nodes_visitats = 0
        temps_inicial = time.time()        
        
        # Frontera y conjunto de visitados vacío
        # En este caso, la frontera es una cola de prioridad
        # que contiene una tupla h(n), estado. Los estados están
        # ordenados de forma asc. en función de h(n)
        self.__frontera = PriorityQueue()
        self.__tancats = set()

        # Primer estado, el inicial, introducido
        self.__frontera.put((estat_inicial.calc_heuristica(), estat_inicial))

        # estat_actual = None
        # Mientres queden estados en la frontera,
        # se extrae de la cola el que menor heurística tenga
        while not self.__frontera.empty():
            _, estat_actual = self.__frontera.get()
            #print(f"Explorando: pos={actual.pos_agents[actual.torn]} f={actual.calc_heuristica()} cami={actual.cami}")

            nodes_visitats += 1

            # Si ese estado ya ha sido visitado, se salta
            if estat_actual in self.__tancats:
                continue
            
            # Si se ha encontrado la meta, se copia el camino
            # acumulado en cami
            if estat_actual.es_meta():
                self.__cami_exit = estat_actual.cami
                
                temps_total = time.time() - temps_inicial
                print(f"Temps total: {temps_total}")
                print(f"Nodes visitats: {nodes_visitats}")
                
                return True 

            # Se expande el estado actual con una lista
            # de sucesores legales tras aplicar cada acción
            # posible. Añadidos al final de la cola.
            for f in estat_actual.genera_fills(2):
                self.__frontera.put((f.calc_heuristica(), f))
                #print(f"→ Añadido a frontera: pos={estat_f.pos_agents[estat_f.torn]} f={estat_f.calc_heuristica()} cami={estat_f.cami}")

            # Marcar el estado que se acaba de evaluar como cerrado
            self.__tancats.add(estat_actual)
        
        # No hay solución o camino legal
        return False

    def actua(self, percepcio: dict):
        # Si aún no existe un plan, construye un estado inicial
        # a partir de la percepción ("foto") del entorno        
        if self.__cami_exit is None:
            estat_inicial = Estat(
                parets = set(percepcio["PARETS"]),
                desti = tuple(percepcio["DESTI"]),
                pos_agents = dict(percepcio["AGENTS"]),
                mida = tuple(percepcio["MIDA"]),
                torn = self.nom
            )

            trobat = self.cerca(estat_inicial)
            if not trobat:
                # No hay solución, no se hace nada
                return "ESPERAR", None

        # Si hay plan, se sigue con el siguiente paso
        if self.__cami_exit:
            accio, direccio = self.__cami_exit.pop(0)
            return accio, direccio
        else: 
            return "ESPERAR", None