import './grouped-connections.css';
import { useBlockDiagram } from '../../composables';
import { Connection } from '../connection/connection';
import { NewConnection } from '../new-connection/new-connection';
import { ConnectionsQueueTransition } from '../connections-queue-transition/connections-queue-transition';
import { getGroupConnectionSlotName } from '../../utils';
import type {
	GroupedConnections as TGroupedConnections,
	ConnectionGroupNames,
} from '../../types';

type GroupedConnectionsSetup = {
	groupedConnections: TGroupedConnections;
	connectionGroupNames: ConnectionGroupNames;
	getGroupConnectionSlotName: typeof getGroupConnectionSlotName;
};

// @vue/component
export const GroupedConnections = {
	name: 'grouped-connections',
	components: {
		Connection,
		NewConnection,
		ConnectionsQueueTransition,
	},
	setup(): GroupedConnectionsSetup
	{
		const {
			groupedConnections,
			connectionGroupNames,
		} = useBlockDiagram();

		return {
			groupedConnections,
			connectionGroupNames,
			getGroupConnectionSlotName,
		};
	},
	template: `
		<ConnectionsQueueTransition>
			<slot
				v-for="connection in connectionGroupNames"
				:key="connection"
				:name="getGroupConnectionSlotName(connection)"
				:connections="groupedConnections[connection]"
			/>
			<NewConnection/>
		</ConnectionsQueueTransition>
	`,
};
