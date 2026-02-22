from practica import joc
#from . import agent_amplada
#from . import agent_A_estrella
#from . import agent_minimax
from practica import agent_amplada, agent_A_estrella, agent_minimax


def main():
    mida = (10, 10)

    agents = [
        #agent_amplada.Viatger("AGENT_1")
        agent_A_estrella.Viatger("AGENT_1")
    ]

    agent = [
        agent_minimax.Viatger("AGENT_1"), 
        agent_minimax.Viatger("AGENT_2")]

    lab = joc.Laberint(agent, mida_taulell=mida)
    lab.comencar()


if __name__ == "__main__":
    main()
