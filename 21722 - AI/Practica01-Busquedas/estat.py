import copy

class Estat:
    """
    Clase que representa el estado del juego en cada momento.

    Contiene la información del tablero (paredes, tamaño), la posición de los agentes,
    turno actual camino recorrido hasta el estado actual...
    """

    # Conjunto de acciones posibles (acción, dirección)
    accions = [("MOURE","N"),("MOURE","S"),("MOURE","E"),("MOURE","O"),
               ("BOTAR","N"),("BOTAR","S"),("BOTAR","E"),("BOTAR","O"),
               ("POSAR_PARET","N"),("POSAR_PARET","S"),("POSAR_PARET","E"),("POSAR_PARET","O")]

    def __init__(self, parets: set[tuple[int, int]], desti: tuple[int, int], 
                 pos_agents: dict[str, tuple[int, int]], mida: tuple[int, int], torn: str = None, cami=None):
        
        """
        Inicializa un nuevo estado del juego

        Args:
            parets: conjunto de cordenadas donde hay paredes
            desti: posición de la meta
            pos_agents: diccionario con la posición de cada agente
            mida: tamaño del tablero m x n
            torn: nombre del agente que tiene el turno actual
            cami: lista de (acción, dirección) que nos han llevado hasta el estado actual
        """
        self.parets = parets
        self.desti = desti
        self.pos_agents = dict(pos_agents)                              
        self.mida = mida                    
        self.torn = torn
        self.costAcumulat = 0 # tomado en cuenta en A*

        if cami is None:
            self.cami = []
        else:
            self.cami = cami      

    def _es_legal(self, pos: tuple[int, int], agent: str, x: int, y: int) -> bool:
        """
        Comprueba si una decisión es válida para la situación actual del agente

        Args:
            pos: tupla de coordenadas de la posición a validar
            agent: agente que va a hacer el movimiento
            x, y: coordenadas (por separado) de la nueva posición candidata

            * pos + x,y representan la misma coordenada, pero tenerla en diferentes
            formatos facilita algunas comprobaciones

        Returns:
            True si la posición está dentro de los límites del tablero,
            sin pared y sin algún otro agente
        """
        dins_limits = (0 <= x < self.mida[0]) and (0 <= y < self.mida[1])       # Comprovar si està dins dels límits
        hi_ha_paret = pos in self.parets                                   # Comprovar si hi ha paret
        hi_ha_agent = pos in [pos for ag, pos in self.pos_agents.items() if ag != agent] # Comprovar col·lisió d'agents
        return dins_limits and not hi_ha_paret and not hi_ha_agent

    def _calcula_posicio(self, agent: str, direccio: str, unitats) -> tuple[tuple[int, int], bool]:
        """
        Calcula la nueva posición dada una dirección

        Args:
            agent: Agente que actúa
            direccio: Dirección del movimiento ('N', 'S', 'E', 'O').
            unitats: Número de casillas .

        Returns:
            (nova_pos, legal): tupla con la nueva posición y
            comprobación de si el movimiento es válido
        """
        x, y = self.pos_agents[agent]

        match direccio:
            case "N":
                y -= unitats
            case "S":
                y += unitats
            case "E":
                x += unitats
            case "O":
                x -= unitats

        nova_pos = (x, y)
        legal = self._es_legal(nova_pos, agent, x, y)
        return nova_pos, legal

    def es_meta(self) -> bool:
        """
        Método que comprueba si el agente actual ha llegado a la meta
        
        Returns:
            True si la posición actual coincide con la del destino
        """          
        return self.pos_agents[self.torn] == self.desti
        
    def transicio(self, accio, intercanvi):
        """
        Aplica una acción sobre el estado actual y deuvleve el estado
        resultante

        Args:
            accio: tupla (acción, dirección)
            intercanvi: booleano que indica si se ha de intercambiar
                        el turno al final o no (caso minimax)

        Returns:
            (nou_estat, es_legal): estado resultante y booleano de acción válida
        """
        nou_estat = Estat (
            parets=set(self.parets),
            desti=self.desti,
            pos_agents=dict(self.pos_agents),
            mida=self.mida,
            torn=self.torn,
            cami=list(self.cami)
        )

        # separar la tupla de acción dirección en dos variables
        jugada, direccio = accio
        agent_actual = nou_estat.torn

        match jugada:
            case "MOURE":
                nova_pos, legal = nou_estat._calcula_posicio(agent_actual, direccio, 1)
                if legal:
                    # Dejar una pared en la últ. casilla visitada
                    pos_antiga = nou_estat.pos_agents[agent_actual]
                    nou_estat.parets.add(pos_antiga)

                    # Actualizar la posición del agente
                    nou_estat.pos_agents[agent_actual] = nova_pos
            case "BOTAR":
                nova_pos, legal = nou_estat._calcula_posicio(agent_actual, direccio, 2)
                if legal:
                    # Dejar una pared en la últ. casilla visitada
                    pos_antiga = nou_estat.pos_agents[agent_actual]
                    nou_estat.parets.add(pos_antiga)

                    # Actualizar la posición del agente
                    nou_estat.pos_agents[agent_actual] = nova_pos
            case "POSAR_PARET":
                nova_paret, legal = nou_estat._calcula_posicio(agent_actual, direccio, 1)
                
                # Colocar la pared. Además se evita que coloque sobre la meta.
                if legal and nova_paret != nou_estat.desti:
                    # Añadir pared en la lista de paredes
                    nou_estat.parets.add(nova_paret)
                else:
                    # En la condición anterior, aunque se evite colocar
                    # una pared sobre la meta, no se actualiza el valor de
                    # 'legal' así que lo actualizamos
                    legal = False

        # Si se está usando Minimax, cambiamos el turno luego de cada
        # jugada de un agente
        if(intercanvi):
            nou_estat.torn = nou_estat.intercanvi_torn_minimax()
        else:
            nou_estat.torn = agent_actual

        return nou_estat, legal

    def genera_fills(self, agent: int) -> list:
        """
        Genera todos los estados sucesores posibles desde el estado actual

        Args:
            agent (int): indica el tipo de generación
                         1 = Amplitud
                         2 = A*
                         3 = Minimax

        Returns:
            Lista de objetos Estat válidos (hijos/acciones legales)
        """

        fills = []
        for movimentPossible in self.accions:
            if agent == 1 or agent == 2:
                estat_fill, es_legal = self.transicio(movimentPossible, False)
            else:
                estat_fill, es_legal = self.transicio(movimentPossible, True)
            
            if es_legal:
                # Registrar la acción legal en el camino de
                # estados recorrido hasta el actual
                estat_fill.cami = list(self.cami)
                estat_fill.cami.append(movimentPossible)

                # En el caso de A*, se tiene en cuenta el coste de la acción
                if agent == 2:
                    costDarreraAccio = self.cost_accio(movimentPossible)
                    estat_fill.costAcumulat = self.costAcumulat + costDarreraAccio

                # Añadir el hijo a la lista de acciones válidas desde el estado actual
                fills.append(estat_fill)
        return fills

    def cost_accio(self, accio: tuple[str, str]) -> int:
        """
        Asigna un coste a cada tipo de acción

        Args:
            accio: Tupla (acción, dirección)

        Returns:
            Coste (int) de la acción
        """
        a = accio[0]
        if a == "MOURE":
            return 1
        elif a == "BOTAR":
            return 2
        elif a == "POSAR_PARET":
            return 3
        return 1
    
    def calc_heuristica(self) -> int:
        """
        Calcula la heurística f = g + h para A*

        Returns:
            Valor entero de la función heurística

        Comentarios:
            La función de heurística es la distancia Manhattan
            desde el agente hasta la meta
        """

        x, y = self.pos_agents[self.torn]
        dx = abs(x - self.desti[0])
        dy = abs(y - self.desti[1])
        h = dx + dy
        g = self.costAcumulat
        f = g + h
        #print(f"[DEBUG] pos={self.pos_agents[self.torn]} g={g} h={h} f={f}")
        return f
    
    def __lt__(self, other):
        """
        Comparación trivial para evitar errores de comparación de objetos Estat en PriorityQueue.
        """
        return False
    
    def heuristica_minimax(self, max_actual: str) -> int:
        """
        Heurística para el agente adversarial

        Args:
            max_actual: nombre del agente que está jugando como MAX

        Returns:
            (int) heurística: diferencia de distancias Manhattan de los agentes a la meta

        Comentarios:
            Sobre la heurística:
                - valor positivo, ventaja para MAX (nosotros)
                - valor negativo, ventaja para MIN (contrincante)

            Los valores positivos son valorados por encima de los negativos,
            pues el agente prioriza maximizar la distancia del enemigo a la meta.
            Dicho en otras palabras, aquellos estados hijos con heurística mayor
            terminan siendo los elegidos
        """
        # Como ambos agentes calculan su heurística, se controla qué agente ha llamado al método
        # para determinar quién es MIN y quién es MAX
        min_actual = "AGENT_2" if max_actual == "AGENT_1" else "AGENT_1"
        x1, y1 = self.pos_agents[max_actual]
        x2, y2 = self.pos_agents[min_actual]

        # Cálculo de distancia Manhattan de los dos agentes hasta la meta
        dx1 = abs(x1 - self.desti[0]) + abs(y1 - self.desti[1])
        dx2 = abs(x2 - self.desti[0]) + abs(y2 - self.desti[1])
        
        return (dx2 - dx1)

    def intercanvi_torn_minimax(self):
        """
        Alterna el nombre entre AGENT_1 y AGENT_2

        Returns:
            nombre del próximo agente
        """
        return "AGENT_2" if self.torn == "AGENT_1" else "AGENT_1"
    
    def __str__(self):
        """
        Representación textual del estado (debug)
        """
        return (f"{self.torn}: {self.pos_agents[self.torn]} | Accio: {self.cami}")        