from iaLib import agent
import numpy as np
import random
from gymnasium.core import Env

class AgentGenetico(agent.Agent):
    """
    Algoritmo Genético aplicado a FrozenLake.

    Funcionamiento:
        - Se inicializa una población de posibles soluciones (políticas)
        representadas como cadenas de genes (acciones).
        - En cada generación, se evalúa cada individuo mediante una función
        de fitness que mide qué tan buena es su política en el entorno. Esta
        consiste en evaluar la cantidad de recompensa media obtenida.
        - Se seleccionan padres usando selección por torneo, donde cada 
        "torneo" enfrenta dos individuos y el de mayor fitness pasa a ser padre.
        - A partir de los padres se generan hijos aplicando cruce (crossover),
        combinando partes de dos padres para producir nuevas políticas. El
        criterio de crossover es coger una parte de cada padre a partir de un
        punto elegido aleatoriamente.
        - Los hijos sufren mutaciones aleatorias, cambiando algunas acciones 
        en base a la tasa de mutación.
        - La nueva generación reemplaza a la antigua población.
        - El algoritmo reproduce este ciclo hasta reproducir todas las 
        generaciones.
    """


    def __init__(self, tam_poblacion=50, n_generaciones=100, 
                 n_episodios=50, tasa_mutacion=0.1, seed=0):
        super().__init__(long_memoria=0)

        self.n_estados = 16
        self.n_acciones = 4

        self.tam_poblacion = tam_poblacion      # número de políticas por generación
        self.n_generaciones = n_generaciones    # cantidad total de generaciones
        self.n_episodios = n_episodios          # número de episodios por política para evaluación
        self.tasa_mutacion = tasa_mutacion      # tasa de mutación

        # Inicializa la población
        self.poblacion = np.random.randint(0, self.n_acciones, size=(self.tam_poblacion, self.n_estados))

        self.fitness = np.zeros(tam_poblacion)  # lista de valores fitness para cada política
        self.pi = None                          # mejor política

        np.random.seed(seed)
        random.seed(seed)

    def evaluar_individuo(self, env: Env, politica):
        """
        Evalúa la política mediante simulación de varios episodios.
        Retorna la recompensa media obtenida.
        """
        total_recompensa = 0.0

        for _ in range(self.n_episodios):
            estado, _ = env.reset()
            acabado = False
            while not acabado:
                accion = politica[estado]
                estado, recompensa, fin, truncar, _ = env.step(accion)
                if recompensa == 1: total_recompensa += 1
                acabado = fin or truncar

        return total_recompensa / self.n_episodios

    def seleccionar_padres(self):
        """
        Selección por torneo simple.
        Se escogen dos individuos aleatoriamente y se escogen los que
        tengan mejor fitness.
        """
        padres = []
        for _ in range(self.tam_poblacion):
            i1, i2 = np.random.randint(0, self.tam_poblacion, size=2)
            if self.fitness[i1] > self.fitness[i2]:
                padres.append(self.poblacion[i1])
            else:
                padres.append(self.poblacion[i2])
        return np.array(padres)

    def crossover(self, padre1, padre2):
        """
        Cruce desde un punto aleatorio
        """
        punto = np.random.randint(1, self.n_estados)
        hijo = np.concatenate([padre1[:punto], padre2[punto:]])
        return hijo

    def mutacion(self, individuo):
        """
        Aplica mutación aleatoria según la tasa
        """
        for i in range(self.n_estados):
            if random.random() < self.tasa_mutacion:
                individuo[i] = np.random.randint(0, self.n_acciones)
        return individuo

    def train(self, env: Env):
        """
        Entrena la población de políticas usando Algoritmo Genético
        - Inicializar población (init)
        - Evaluación de la población
        1. Selección de padres
        2. Crossover
        3. Mutación
        4. Evaluación de los nuevos candidatos
        5. Selección de individuos para nueva generación
        """

        # Evaluación de la población
        for i in range(self.tam_poblacion):
            self.fitness[i] = self.evaluar_individuo(env, self.poblacion[i])

        # Guardar la mejor política
        mejor_idx = np.argmax(self.fitness)
        self.pi = self.poblacion[mejor_idx].copy()

        for gen in range(self.n_generaciones):
            # Selección de padres
            padres = self.seleccionar_padres()

            # Crossover y mutación
            nueva_poblacion = []
            for i in range(0, self.tam_poblacion, 2):
                p1 = padres[i]
                p2 = padres[i+1 if i+1 < self.tam_poblacion else 0]
                hijo1 = self.crossover(p1, p2)
                hijo2 = self.crossover(p2, p1)
                hijo1 = self.mutacion(hijo1)
                hijo2 = self.mutacion(hijo2)
                nueva_poblacion.extend([hijo1, hijo2])

            # Evaluación de los nuevos candidatos
            for i in range(self.tam_poblacion):
                self.fitness[i] = self.evaluar_individuo(env, nueva_poblacion[i])

            # Guardar la mejor política
            mejor_idx = np.argmax(self.fitness)
            self.pi = nueva_poblacion[mejor_idx].copy()

            # Selección de individuos para nueva generación
            self.poblacion = np.array(nueva_poblacion[:self.tam_poblacion])

            # print(f"Generación {gen+1}/{self.n_generaciones}, mejor fitness: {self.fitness[mejor_idx]:.3f}")

        return self.pi

    def actua(self, estado):
        """
        Devuelve la acción según la mejor política encontrada
        """
        return self.pi[estado]
