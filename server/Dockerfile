FROM mcr.microsoft.com/dotnet/aspnet:9.0 AS base
WORKDIR /app

FROM mcr.microsoft.com/dotnet/sdk:9.0 AS build
WORKDIR /src

# copy csproj and restore as distinct layers
COPY IoT-opsamling.csproj .
RUN dotnet restore "IoT-opsamling.csproj"

# copy the rest of the source and build
COPY . .
RUN dotnet publish "IoT-opsamling.csproj" -c Release -o /app/publish /p:UseAppHost=false

FROM base AS final
WORKDIR /app
COPY --from=build /app/publish .
ENTRYPOINT ["dotnet", "IoT-opsamling.dll"]
