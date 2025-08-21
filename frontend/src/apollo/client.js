import { ApolloClient, InMemoryCache, createHttpLink } from "@apollo/client/core";
import { setContext } from "@apollo/client/link/context";

// HTTP connection to the API
const httpLink = createHttpLink({
    uri: "http://localhost:8888/graphql",
});

// Auth link for future authentication support
const authLink = setContext((_, { headers }) => {
    // Return the headers to the context so httpLink can read them
    return {
        headers: {
            ...headers,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
    };
});

// Cache implementation
const cache = new InMemoryCache({
    typePolicies: {
        RecipePaginator: {
            fields: {
                data: {
                    // Enable infinite scroll by merging pages
                    merge(existing = [], incoming = []) {
                        return [...existing, ...incoming];
                    },
                },
            },
        },
    },
});

// Create the Apollo client
const apolloClient = new ApolloClient({
    link: authLink.concat(httpLink),
    cache,
    defaultOptions: {
        watchQuery: {
            errorPolicy: "ignore",
        },
        query: {
            errorPolicy: "all",
        },
    },
});

export default apolloClient;
